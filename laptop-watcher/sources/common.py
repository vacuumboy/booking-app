from __future__ import annotations

import html
import json
import re
from dataclasses import dataclass

import httpx

USER_AGENT = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
)

DEFAULT_HEADERS = {
    "User-Agent": USER_AGENT,
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Language": "lv-LV,lv;q=0.9,ru;q=0.8,en;q=0.7",
}


@dataclass
class RawListing:
    url: str
    title: str
    price: float | None
    source: str
    store: str
    text_blob: str = ""


def is_blocked(page: str, status_code: int) -> bool:
    if status_code in (403, 503):
        return True
    lowered = page[:8000].lower()
    return any(
        marker in lowered
        for marker in (
            "just a moment",
            "mazliet uzgaidiet",  # Cloudflare LV
            "security verification",
            "cf-browser-verification",
            "access denied",
            "attention required",
        )
    )


def fetch_page(url: str, client: httpx.Client) -> tuple[int, str] | None:
    try:
        response = client.get(url)
    except httpx.HTTPError:
        return None
    if is_blocked(response.text, response.status_code):
        return None
    if response.status_code >= 400:
        return None
    return response.status_code, response.text


def decode_json_ld_scripts(page_html: str) -> list[dict]:
    pattern = re.compile(
        r'<script[^>]*type=["\']application/ld[^"\']+["\'][^>]*>(.*?)</script>',
        re.DOTALL | re.IGNORECASE,
    )
    items: list[dict] = []
    for match in pattern.finditer(page_html):
        raw = html.unescape(match.group(1))
        try:
            payload = json.loads(raw)
        except json.JSONDecodeError:
            continue
        if isinstance(payload, dict):
            items.append(payload)
    return items


def extract_item_list(page_html: str) -> list[tuple[str, str]]:
    """Returns (url, title) pairs from schema.org ItemList blocks."""
    results: list[tuple[str, str]] = []
    seen: set[str] = set()
    for block in decode_json_ld_scripts(page_html):
        main = block.get("mainEntity")
        if not isinstance(main, dict):
            continue
        elements = main.get("itemListElement")
        if not isinstance(elements, list):
            continue
        for element in elements:
            if not isinstance(element, dict):
                continue
            url = element.get("item") or element.get("url")
            title = element.get("name")
            if not url or not title:
                continue
            url = str(url)
            if url in seen:
                continue
            seen.add(url)
            results.append((url, _clean_text(str(title))))
    return results


def extract_links(page_html: str, pattern: re.Pattern[str], base_url: str = "") -> list[str]:
    seen: set[str] = set()
    urls: list[str] = []
    for match in pattern.finditer(page_html):
        href = match.group(1)
        if href.startswith("/"):
            href = base_url.rstrip("/") + href
        if href in seen:
            continue
        seen.add(href)
        urls.append(href)
    return urls


def listing_from_html(
    url: str,
    page: str,
    source: str,
    store: str,
    seed_title: str = "",
) -> RawListing:
    title = _extract_title(page) or seed_title or url
    price = _extract_price(page)
    text_blob = _extract_specs_text(page, seed_title)
    return RawListing(
        url=url,
        title=_clean_text(title),
        price=price,
        source=source,
        store=store,
        text_blob=text_blob,
    )


def enrich_product_page(
    url: str,
    source: str,
    store: str,
    client: httpx.Client,
    seed_title: str = "",
) -> RawListing | None:
    fetched = fetch_page(url, client)
    if fetched is None:
        return None
    _, page = fetched
    return listing_from_html(url, page, source, store, seed_title=seed_title)


def _clean_text(value: str) -> str:
    value = html.unescape(value)
    value = re.sub(r"<[^>]+>", " ", value)
    return re.sub(r"\s+", " ", value).strip()


def _extract_title(page: str) -> str | None:
    for block in decode_json_ld_scripts(page):
        if block.get("@type") == "Product" and block.get("name"):
            return str(block["name"])
        main = block.get("mainEntity")
        if isinstance(main, dict) and main.get("@type") == "Product" and main.get("name"):
            return str(main["name"])

    match = re.search(r'<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)', page, re.I)
    if match:
        return match.group(1)
    return _extract_tag_text(page, "h1")


def _extract_tag_text(page: str, tag: str) -> str | None:
    match = re.search(rf"<{tag}[^>]*>(.*?)</{tag}>", page, re.DOTALL | re.IGNORECASE)
    if not match:
        return None
    return _clean_text(match.group(1))


def _extract_price(page: str) -> float | None:
    """Extract full laptop price; ignore leasing teasers like 1€/mo."""

    # 1) schema.org Offer on the product itself — most trustworthy
    for block in decode_json_ld_scripts(page):
        for node in (block, block.get("mainEntity")):
            if not isinstance(node, dict):
                continue
            offers = node.get("offers")
            offer_nodes: list[dict] = []
            if isinstance(offers, dict):
                offer_nodes = [offers]
            elif isinstance(offers, list):
                offer_nodes = [o for o in offers if isinstance(o, dict)]
            for offer in offer_nodes:
                value = _coerce_laptop_price(offer.get("price"))
                if value is not None:
                    return value

    # 2) Explicit product price meta / full-price fields
    for pattern in (
        r'data-full-price=["\']([0-9.]+)',
        r'<meta[^>]+property=["\']product:price:amount["\'][^>]+content=["\']([0-9.]+)',
        r'itemprop="price"[^>]+content="([0-9.]+)"',
    ):
        match = re.search(pattern, page, re.IGNORECASE)
        if match:
            value = _coerce_laptop_price(match.group(1))
            if value is not None:
                return value

    # 3) Last resort: visible price spans (may include accessories — take max)
    candidates: list[float] = []
    for pattern in (
        r'<span class="price">([0-9]+(?:[.,][0-9]+)?)</span>',
        r'data-price=["\']([0-9.]+)',
    ):
        for match in re.finditer(pattern, page, re.IGNORECASE):
            value = _coerce_laptop_price(match.group(1))
            if value is not None:
                candidates.append(value)
    if candidates:
        return max(candidates)
    return None


def _coerce_laptop_price(raw: object) -> float | None:
    if raw is None or raw == "":
        return None
    try:
        value = float(str(raw).replace(",", ".").replace(" ", ""))
    except (TypeError, ValueError):
        return None
    # 1€ / 12€/mo teasers and absurd outliers
    if value < 250 or value > 5000:
        return None
    return value


def _extract_specs_text(page: str, seed_title: str = "") -> str:
    chunks: list[str] = [seed_title]
    match = re.search(
        r'<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)',
        page,
        re.IGNORECASE,
    )
    if match:
        chunks.append(html.unescape(match.group(1)))

    for block in decode_json_ld_scripts(page):
        for node in (block, block.get("mainEntity")):
            if not isinstance(node, dict):
                continue
            if node.get("description"):
                chunks.append(str(node["description"]))

    plain = re.sub(r"<[^>]+>", " ", page)
    plain = re.sub(r"\s+", " ", plain)
    for pattern in (
        r"atsvaidzes intensitāte.{0,60}",
        r"ekrāna atsvaidzes.{0,60}",
        r"refresh rate.{0,60}",
        r"displeja atsvaidze.{0,60}",
        r"Svars.{0,40}",
        r"produkta svars.{0,40}",
        r"\d(?:[.,]\d+)?\s*kg",
        r"\d(?:[.,]\d+)?\s*кг",
    ):
        for hit in re.findall(pattern, plain, re.IGNORECASE):
            chunks.append(hit)

    return " ".join(chunks)[:12000]
