from __future__ import annotations

import time
from dataclasses import dataclass, field
from typing import Any

from sources.common import RawListing
from sources.stores import STORES, scan_manual_urls, scan_store


@dataclass
class ScanReport:
    ok_stores: list[str] = field(default_factory=list)
    fail_stores: list[str] = field(default_factory=list)
    scanned: int = 0
    catalog_links: int = 0


def _limit(value: Any, default: int, unlimited: int = 200) -> int:
    """None / 0 / negative / 'unlimited' → высокий потолок (не бесконечный цикл)."""
    if value is None:
        return unlimited
    if isinstance(value, str) and value.strip().lower() in {"", "unlimited", "none", "null", "inf"}:
        return unlimited
    number = int(value)
    if number <= 0:
        return unlimited
    return number


def collect_raw_listings(
    config: dict,
    heartbeat: Any | None = None,
) -> tuple[list[RawListing], ScanReport]:
    scan_cfg = config.get("scan", {})
    max_pages = _limit(scan_cfg.get("max_pages_per_source"), 100)
    max_enrich = _limit(scan_cfg.get("max_enrich_per_source"), 10_000)
    request_delay = float(scan_cfg.get("request_delay_sec", 0.25))

    filters = config.get("filters", {})
    exclude_brands = list(filters.get("exclude_brands", []))
    exclude_keywords = list(filters.get("exclude_keywords", []))

    candidates: list[RawListing] = []
    report = ScanReport()
    sources_cfg: dict[str, Any] = config.get("sources", {})

    for store_id, store in STORES.items():
        store_cfg = sources_cfg.get(store_id, {})
        if not store_cfg.get("enabled", False):
            continue

        store_pages = _limit(store_cfg.get("max_pages", max_pages), max_pages)
        store_enrich = _limit(store_cfg.get("max_enrich", max_enrich), max_enrich)

        print(
            f"Сканирую {store.name} ({store.installment_note}) "
            f"[pages≤{store_pages}, enrich≤{store_enrich}]...",
            flush=True,
        )
        items, error = scan_store(
            store_id,
            max_pages=store_pages,
            max_enrich=store_enrich,
            request_delay=request_delay,
            exclude_brands=exclude_brands,
            exclude_keywords=exclude_keywords,
            heartbeat=heartbeat,
        )
        if error:
            print(f"  ⚠ {store.name}: {error}", flush=True)
            report.fail_stores.append(store.name)
            continue
        print(f"  ✓ {store.name}: проверено карточек {len(items)}", flush=True)
        report.ok_stores.append(store.name)
        candidates.extend(items)
        time.sleep(0.2)

    dateks_urls_cfg = sources_cfg.get("dateks_urls", {})
    if dateks_urls_cfg.get("enabled", True):
        urls = list(dateks_urls_cfg.get("urls", []))
        if urls:
            print("Проверяю Dateks (ручные ссылки)...", flush=True)
            items = scan_manual_urls(
                urls,
                source="dateks",
                store="Dateks",
                request_delay=request_delay,
                heartbeat=heartbeat,
            )
            found = len(items)
            if found < len(urls):
                print(f"  ⚠ Dateks manual: прочитано {found}/{len(urls)} ссылок", flush=True)
            else:
                print(f"  ✓ Dateks manual: {found} ссылок", flush=True)
            candidates.extend(items)

    report.scanned = len(candidates)
    return candidates, report
