from __future__ import annotations

import html
import json
import re
from dataclasses import dataclass

import httpx

USER_AGENT = (
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
)


@dataclass
class RawListing:
    url: str
    title: str
    price: float | None
    source: str
    store: str
    text_blob: str = ""


def _decode_json_ld_scripts(page_html: str) -> list[dict]:
    pattern = re.compile(
        r'<script type="application/ld\+json">(.*?)</script>',
        re.DOTALL,
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


def fetch_euronics_listings(list_url: str) -> list[RawListing]:
    with httpx.Client(headers={"User-Agent": USER_AGENT}, timeout=45, follow_redirects=True) as client:
        response = client.get(list_url)
        response.raise_for_status()
        page = response.text

    listings: list[RawListing] = []
    for block in _decode_json_ld_scripts(page):
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
            listings.append(
                RawListing(
                    url=str(url),
                    title=str(title),
                    price=None,
                    source="euronics",
                    store="Euronics",
                )
            )
    return listings


def enrich_euronics_listing(url: str) -> RawListing | None:
    with httpx.Client(headers={"User-Agent": USER_AGENT}, timeout=45, follow_redirects=True) as client:
        response = client.get(url)
        response.raise_for_status()
        page = response.text

    title = _extract_tag_text(page, "h1") or url
    price = _extract_price(page)
    text_blob = _extract_specs_text(page)
    return RawListing(
        url=url,
        title=title,
        price=price,
        source="euronics",
        store="Euronics",
        text_blob=text_blob,
    )


def fetch_dateks_url(url: str) -> RawListing | None:
    with httpx.Client(headers={"User-Agent": USER_AGENT}, timeout=45, follow_redirects=True) as client:
        response = client.get(url)
        if response.status_code != 200:
            return None
        page = response.text.lower()
        if "just a moment" in page or "security verification" in page:
            return None

    title_match = re.search(r"<h1[^>]*>(.*?)</h1>", page, re.DOTALL | re.IGNORECASE)
    title = re.sub(r"<[^>]+>", "", title_match.group(1)).strip() if title_match else url
    price = _extract_price(page)
    text_blob = re.sub(r"<[^>]+>", " ", page)
    text_blob = re.sub(r"\s+", " ", text_blob)
    return RawListing(
        url=url,
        title=title,
        price=price,
        source="dateks",
        store="Dateks",
        text_blob=text_blob[:8000],
    )


def _extract_tag_text(page: str, tag: str) -> str | None:
    match = re.search(rf"<{tag}[^>]*>(.*?)</{tag}>", page, re.DOTALL | re.IGNORECASE)
    if not match:
        return None
    return re.sub(r"<[^>]+>", "", match.group(1)).strip()


def _extract_price(page: str) -> float | None:
    for block in _decode_json_ld_scripts(page):
        main = block.get("mainEntity")
        if isinstance(main, dict):
            offers = main.get("offers")
            if isinstance(offers, dict) and offers.get("price") is not None:
                return float(offers["price"])

    match = re.search(r'<span class="price">([0-9]+(?:\.[0-9]+)?)</span>', page)
    if match:
        return float(match.group(1))

    match = re.search(r"([0-9]{2,4}[.,][0-9]{2})\s*€", page)
    if match:
        return float(match.group(1).replace(",", "."))

    return None


def _extract_specs_text(page: str) -> str:
    chunks: list[str] = []
    for block in _decode_json_ld_scripts(page):
        main = block.get("mainEntity")
        if isinstance(main, dict):
            if main.get("name"):
                chunks.append(str(main["name"]))
            if main.get("description"):
                chunks.append(str(main["description"]))

    for pattern in (
        r"atsvaidzes intensitāte[^<]{0,40}",
        r"refresh rate[^<]{0,40}",
        r"Svars[^<]{0,40}",
        r"weight[^<]{0,40}",
        r"120\s*Hz",
        r"120\s*hz",
    ):
        for match in re.findall(pattern, page, re.IGNORECASE):
            chunks.append(match)

    return " ".join(chunks)
