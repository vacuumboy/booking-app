from __future__ import annotations

import re
import time
from dataclasses import dataclass

import httpx

from sources.common import (
    DEFAULT_HEADERS,
    RawListing,
    enrich_product_page,
    extract_item_list,
    extract_links,
    fetch_page,
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
            r'href="(/lv/[^"]*portativ[^"]*\.html)"',
            re.IGNORECASE,
        ),
    ),
    "dateks": StoreDefinition(
        id="dateks",
        name="Dateks",
        installment_note="Inbank līdz 48 mēn.",
        list_url="https://www.dateks.lv/cenas/portativie-datori",
        base_url="https://www.dateks.lv",
        link_pattern=re.compile(
            r'href="(/cenas/portativie-datori/[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
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
        list_url="https://aio.lv/lv/portativie-un-personalie-datori/portativie-datori",
        base_url="https://aio.lv",
        link_pattern=re.compile(
            r'href="(/lv/[^"]*portativ[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
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
            r'href="(/lv/portativie-datori/[^"]+)"',
            re.IGNORECASE,
        ),
        page_param="page",
    ),
}


def _page_url(store: StoreDefinition, page: int) -> str:
    if page <= 1:
        return store.list_url
    sep = "&" if "?" in store.list_url else "?"
    return f"{store.list_url}{sep}{store.page_param}={page}"


def _should_enrich(title: str, exclude_brands: list[str], exclude_keywords: list[str]) -> bool:
    text = title.lower()
    for brand in exclude_brands:
        if brand.lower() in text:
            return False
    for keyword in exclude_keywords:
        if keyword.lower() in text:
            return False
    if re.search(r'\b(16|17|18)(?:[.,]\d)?\s*["\']', text):
        return False
    if re.search(r"\b1[6-8]\s*coll", text):
        return False
    return True


def _listing_seed(title: str) -> RawListing:
    return RawListing(url="", title=title, price=None, source="", store="", text_blob=title)


def collect_catalog_listings(
    store: StoreDefinition,
    client: httpx.Client,
    max_pages: int,
) -> tuple[list[tuple[str, str]], str | None]:
    """Returns ([(url, title), ...], error_message)."""
    pairs: list[tuple[str, str]] = []
    seen: set[str] = set()

    for page in range(1, max_pages + 1):
        fetched = fetch_page(_page_url(store, page), client)
        if fetched is None:
            if page == 1:
                return [], "сайт недоступен (бот/Cloudflare)"
            break
        _, html_page = fetched

        if store.uses_json_ld_list:
            page_pairs = extract_item_list(html_page)
        elif store.link_pattern is not None:
            urls = extract_links(html_page, store.link_pattern, store.base_url)
            page_pairs = [(url, "") for url in urls]
        else:
            page_pairs = []

        if not page_pairs:
            if page == 1:
                return [], "не нашёл товары на странице каталога"
            break

        for url, title in page_pairs:
            if url in seen:
                continue
            seen.add(url)
            pairs.append((url, title))

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

        enrich_budget = max_enrich
        queued: list[tuple[int, str, str]] = []
        for url, title in pairs:
            if not _should_enrich(title, exclude_brands, exclude_keywords):
                continue
            queued.append((_enrich_priority(title), url, title))
        queued.sort(key=lambda item: item[0])

        for _, url, title in queued:
            if enrich_budget <= 0:
                break
            enriched = enrich_product_page(
                url,
                store.id,
                store.name,
                client,
                seed_title=title,
            )
            if enriched is None:
                continue
            listings.append(enriched)
            enrich_budget -= 1
            time.sleep(request_delay)

    return listings, None


def scan_manual_urls(
    urls: list[str],
    source: str,
    store: str,
    request_delay: float,
) -> list[RawListing]:
    listings: list[RawListing] = []
    with httpx.Client(headers=DEFAULT_HEADERS, timeout=45, follow_redirects=True) as client:
        for url in urls:
            item = enrich_product_page(url, source, store, client)
            if item is not None:
                listings.append(item)
            time.sleep(request_delay)
    return listings
