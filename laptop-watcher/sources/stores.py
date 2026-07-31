from __future__ import annotations

import re
import time
from dataclasses import dataclass

import httpx

from sources.browser import BROWSER_STORE_IDS, browser_available, fetch_page_browser, fetch_pages_browser
from sources.common import (
    DEFAULT_HEADERS,
    RawListing,
    enrich_product_page,
    extract_item_list,
    extract_links,
    fetch_page,
    listing_from_html,
)


@dataclass(frozen=True)
class StoreDefinition:
    id: str
    name: str
    installment_note: str
    list_url: str
    base_url: str = ""
    link_pattern: re.Pattern[str] | None = None
    page_param: str = "page"  # tet uses p=
    uses_json_ld_list: bool = False
    use_browser: bool = False
    # If set, page N uses this format: "{list_url}/{page}"
    path_pagination: bool = False
    # Optional template for page>1, e.g. ".../{page}/f:stock-y"
    page_url_template: str = ""


# Latvijas interneta veikali ar nomaksu / līzingu (pēc iespējas pilns saraksts).
STORES: dict[str, StoreDefinition] = {
    "euronics": StoreDefinition(
        id="euronics",
        name="Euronics",
        installment_note="Inbank līdz 60 mēn.",
        list_url="https://www.euronics.lv/it/portativie-datori/klepjdatori",
        uses_json_ld_list=True,
    ),
    "tet": StoreDefinition(
        id="tet",
        name="Tet",
        installment_note="Nomaksa līdz 36 mēn.",
        list_url="https://www.tet.lv/veikals/datortehnika/portativie-datori-un-piederumi/portativie-datori.html",
        base_url="https://www.tet.lv",
        link_pattern=re.compile(
            r'href="(/veikals/portativie-datori/[^"]+\.html)"',
            re.IGNORECASE,
        ),
        page_param="p",
    ),
    "220": StoreDefinition(
        id="220",
        name="220.lv",
        installment_note="0% nomaksa, Inbank",
        list_url="https://220.lv/lv/datortehnika/portativie-datori-un-plansetes/portativie-datori",
        base_url="https://220.lv",
        link_pattern=re.compile(
            r'href="((?:https://220\.lv)?/lv/[^"]*(?:portativ|laptop|klepj)[^"]*)"',
            re.IGNORECASE,
        ),
        # Cloudflare challenge ("Mazliet uzgaidiet") — browser тоже часто не проходит.
        use_browser=True,
    ),
    "dateks": StoreDefinition(
        id="dateks",
        name="Dateks",
        installment_note="Inbank līdz 48 mēn.",
        list_url="https://www.dateks.lv/cenas/portativie-datori",
        base_url="https://www.dateks.lv",
        link_pattern=re.compile(
            r'href="((?:https://www\.dateks\.lv)?/cenas/portativie-datori/\d+-[^"?#]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
        use_browser=True,
    ),
    "rd": StoreDefinition(
        id="rd",
        name="RD Electronics",
        installment_note="Līzings līdz 60 mēn.",
        list_url="https://www.rdveikals.lv/lv/datori-un-periferija/portativie-datori",
        base_url="https://www.rdveikals.lv",
        link_pattern=re.compile(
            r'href="(/lv/[^"]*portativ[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
    ),
    "1a": StoreDefinition(
        id="1a",
        name="1a.lv",
        installment_note="Esto / Inbank",
        list_url="https://www.1a.lv/c/datortehnika/portativie-datori",
        base_url="https://www.1a.lv",
        link_pattern=re.compile(
            r'href="(/c/datortehnika/portativie-datori/[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
    ),
    "aio": StoreDefinition(
        id="aio",
        name="AiO.lv",
        installment_note="Klix / Esto bezprocentu",
        list_url="https://aio.lv/lv/category--portativie-datori--94",
        base_url="https://aio.lv",
        link_pattern=re.compile(
            r'href="((?:https://aio\.lv)?/lv/product--[^"?#]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
        use_browser=True,
    ),
    "elkor": StoreDefinition(
        id="elkor",
        name="Elkor",
        installment_note="Nomaksa veikalā / online",
        list_url="https://www.elkor.lv/lv/datori/portativie-datori/",
        base_url="https://www.elkor.lv",
        link_pattern=re.compile(
            r'href="(/lv/datori/portativie-datori/[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
    ),
    "signe": StoreDefinition(
        id="signe",
        name="Signe.lv",
        installment_note="Nomaksa",
        list_url="https://signe.lv/lv/datori/portativie-datori",
        base_url="https://signe.lv",
        link_pattern=re.compile(
            r'href="(/lv/datori/portativie-datori/[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
    ),
    "kursi": StoreDefinition(
        id="kursi",
        name="Kursi.lv",
        installment_note="Nomaksa",
        list_url="https://www.kursi.lv/lv/datori/portativie-datori",
        base_url="https://www.kursi.lv",
        link_pattern=re.compile(
            r'href="(/lv/datori/portativie-datori/[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
    ),
    "bigbox": StoreDefinition(
        id="bigbox",
        name="Bigbox.lv",
        installment_note="Nomaksa",
        list_url="https://www.bigbox.lv/lv/portativie-datori",
        base_url="https://www.bigbox.lv",
        link_pattern=re.compile(
            r'href="(/lv/portativie-datori/[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
    ),
    "balticdata": StoreDefinition(
        id="balticdata",
        name="Baltic Data",
        installment_note="Nomaksa",
        list_url="https://balticdata.lv/lv/portativie-datori",
        base_url="https://balticdata.lv",
        link_pattern=re.compile(
            r'href="((?:https://balticdata\.lv)?/lv/portativie-datori/portativais-dators-[^"?#]+)"',
            re.IGNORECASE,
        ),
        path_pagination=True,
        use_browser=True,
    ),
    "m79": StoreDefinition(
        id="m79",
        name="M79.lv",
        installment_note="Nomaksa / līzings",
        list_url="https://m79.lv/portativiedatori/portativiedatori/f:stock-y",
        base_url="https://m79.lv",
        link_pattern=re.compile(
            # slug starts with a letter — отсекает /2, /3 и /f:stock-y
            r'href="((?:https://m79\.lv)?/portativiedatori/portativiedatori/[a-z][^"#?]*)"',
            re.IGNORECASE,
        ),
        page_url_template="https://m79.lv/portativiedatori/portativiedatori/{page}/f:stock-y",
    ),
}


def _page_url(store: StoreDefinition, page: int) -> str:
    if page <= 1:
        return store.list_url
    if store.page_url_template:
        return store.page_url_template.format(page=page)
    if store.path_pagination:
        return f"{store.list_url.rstrip('/')}/{page}"
    sep = "&" if "?" in store.list_url else "?"
    return f"{store.list_url}{sep}{store.page_param}={page}"


def _seed_from_url(url: str) -> str:
    slug = url.rstrip("/").split("/")[-1]
    slug = re.sub(r"^\d+-", "", slug)
    slug = re.sub(r"^product--", "", slug)
    slug = re.sub(r"--\d+$", "", slug)
    return slug.replace("-", " ").replace("_", " ")


def _should_enrich(title: str, exclude_brands: list[str], exclude_keywords: list[str], url: str = "") -> bool:
    text = f"{title} {_seed_from_url(url)}".lower()
    for brand in exclude_brands:
        if brand.lower() in text:
            return False
    for keyword in exclude_keywords:
        if keyword.lower() in text:
            return False
    if re.search(r'\b(16|17|18)(?:[.,]\d)?\s*["\']', text):
        return False
    if re.search(r"\b1[6-8]\s*(?:coll|inch)\b", text):
        return False
    return True


def _listing_seed(title: str) -> RawListing:
    return RawListing(url="", title=title, price=None, source="", store="", text_blob=title)


def _pairs_from_html(store: StoreDefinition, html_page: str) -> list[tuple[str, str]]:
    if store.uses_json_ld_list:
        return extract_item_list(html_page)
    if store.link_pattern is not None:
        urls = extract_links(html_page, store.link_pattern, store.base_url)
        # Drop category landing pages themselves.
        urls = [url for url in urls if url.rstrip("/") != store.list_url.rstrip("/")]
        return [(url, "") for url in urls]
    return []


def collect_catalog_listings(
    store: StoreDefinition,
    client: httpx.Client,
    max_pages: int,
) -> tuple[list[tuple[str, str]], str | None]:
    """Returns ([(url, title), ...], error_message)."""
    pairs: list[tuple[str, str]] = []
    seen: set[str] = set()
    used_browser = False

    for page in range(1, max_pages + 1):
        url = _page_url(store, page)
        fetched = fetch_page(url, client)
        if fetched is None and store.use_browser and browser_available():
            if page == 1:
                print(f"  → {store.name}: HTTP заблокирован, пробую Playwright...")
            fetched = fetch_page_browser(url)
            used_browser = fetched is not None
        if fetched is None:
            if page == 1:
                hint = " (Playwright тоже не помог)" if store.use_browser else ""
                return [], f"сайт недоступен (бот/Cloudflare){hint}"
            break
        _, html_page = fetched
        page_pairs = _pairs_from_html(store, html_page)

        if not page_pairs:
            if page == 1:
                return [], "не нашёл товары на странице каталога"
            break

        for item_url, title in page_pairs:
            if item_url in seen:
                continue
            seen.add(item_url)
            pairs.append((item_url, title))

    if used_browser and pairs:
        print(f"  → {store.name}: Playwright открыл каталог ({len(pairs)} ссылок)")
    return pairs, None


def _enrich_priority(title: str) -> int:
    if re.search(r"120\s*hz", title, re.IGNORECASE):
        return 0
    if re.search(r'\b(14|15(?:[.,]\d)?)\s*["\'`]', title):
        return 1
    if re.search(r"\b1[45](?:[.,]\d)?\s*coll", title, re.IGNORECASE):
        return 1
    return 2


def scan_store(
    store_id: str,
    *,
    max_pages: int,
    max_enrich: int,
    request_delay: float,
    exclude_brands: list[str],
    exclude_keywords: list[str],
) -> tuple[list[RawListing], str | None]:
    store = STORES[store_id]
    listings: list[RawListing] = []
    with httpx.Client(headers=DEFAULT_HEADERS, timeout=45, follow_redirects=True) as client:
        pairs, error = collect_catalog_listings(store, client, max_pages)
        if error:
            return [], error

        print(f"  → {store.name}: ссылок в каталоге {len(pairs)}")
        enrich_budget = max_enrich
        queued: list[tuple[int, str, str]] = []
        for url, title in pairs:
            seed = title or _seed_from_url(url)
            if not _should_enrich(seed, exclude_brands, exclude_keywords, url=url):
                continue
            queued.append((_enrich_priority(seed), url, seed))
        queued.sort(key=lambda item: item[0])
        if enrich_budget < len(queued):
            print(
                f"  → {store.name}: после фильтров {len(queued)}, "
                f"открываю {enrich_budget} (лимит)"
            )
            queued = queued[:enrich_budget]
        else:
            print(f"  → {store.name}: открываю все {len(queued)} после фильтров")

        browser_needed: list[tuple[str, str]] = []
        for _, url, title in queued:
            enriched = enrich_product_page(
                url,
                store.id,
                store.name,
                client,
                seed_title=title,
            )
            if enriched is not None:
                if _should_enrich(enriched.title, exclude_brands, exclude_keywords, url=url):
                    listings.append(enriched)
                time.sleep(request_delay)
                continue
            if store.use_browser and browser_available():
                browser_needed.append((url, title))

        if browser_needed:
            print(f"  → {store.name}: Playwright карточки ({len(browser_needed)})...")
            pages = fetch_pages_browser([url for url, _ in browser_needed])
            for url, title in browser_needed:
                html = pages.get(url)
                if not html:
                    continue
                item = listing_from_html(
                    url,
                    html,
                    store.id,
                    store.name,
                    seed_title=title,
                )
                # Dateks иногда отдаёт каталог вместо карточки.
                if item.title.lower() in {"portatīvie datori", "portativie datori", "laptops"}:
                    continue
                if not _should_enrich(item.title, exclude_brands, exclude_keywords, url=url):
                    continue
                listings.append(item)

    return listings, None


def scan_manual_urls(
    urls: list[str],
    source: str,
    store: str,
    request_delay: float,
) -> list[RawListing]:
    listings: list[RawListing] = []
    missing: list[str] = []
    with httpx.Client(headers=DEFAULT_HEADERS, timeout=45, follow_redirects=True) as client:
        for url in urls:
            item = enrich_product_page(url, source, store, client)
            if item is not None:
                listings.append(item)
            else:
                missing.append(url)
            time.sleep(request_delay)

    if missing and browser_available() and source in BROWSER_STORE_IDS:
        print(f"  → {store}: Playwright для {len(missing)} ручных ссылок...")
        pages = fetch_pages_browser(missing)
        for url in missing:
            html = pages.get(url)
            if not html:
                continue
            listings.append(listing_from_html(url, html, source, store))

    return listings
