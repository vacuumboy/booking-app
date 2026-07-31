from __future__ import annotations

import time
from typing import Any

from sources.common import RawListing
from sources.stores import STORES, scan_manual_urls, scan_store


def collect_raw_listings(config: dict) -> list[RawListing]:
    scan_cfg = config.get("scan", {})
    max_pages = int(scan_cfg.get("max_pages_per_source", 3))
    max_enrich = int(scan_cfg.get("max_enrich_per_source", 80))
    request_delay = float(scan_cfg.get("request_delay_sec", 0.35))

    filters = config.get("filters", {})
    exclude_brands = list(filters.get("exclude_brands", []))
    exclude_keywords = list(filters.get("exclude_keywords", []))

    candidates: list[RawListing] = []
    sources_cfg: dict[str, Any] = config.get("sources", {})

    for store_id, store in STORES.items():
        store_cfg = sources_cfg.get(store_id, {})
        if not store_cfg.get("enabled", False):
            continue

        print(f"Сканирую {store.name} ({store.installment_note})...")
        items, error = scan_store(
            store_id,
            max_pages=int(store_cfg.get("max_pages", max_pages)),
            max_enrich=int(store_cfg.get("max_enrich", max_enrich)),
            request_delay=request_delay,
            exclude_brands=exclude_brands,
            exclude_keywords=exclude_keywords,
        )
        if error:
            print(f"  ⚠ {store.name}: {error}")
            continue
        print(f"  ✓ {store.name}: проверено карточек {len(items)}")
        candidates.extend(items)
        time.sleep(0.2)

    dateks_urls_cfg = sources_cfg.get("dateks_urls", {})
    if dateks_urls_cfg.get("enabled", True):
        urls = list(dateks_urls_cfg.get("urls", []))
        if urls:
            print("Проверяю Dateks (ручные ссылки)...")
            items = scan_manual_urls(
                urls,
                source="dateks",
                store="Dateks",
                request_delay=request_delay,
            )
            found = len(items)
            if found < len(urls):
                print(f"  ⚠ Dateks: прочитано {found}/{len(urls)} ссылок")
            else:
                print(f"  ✓ Dateks: {found} ссылок")
            candidates.extend(items)

    return candidates
