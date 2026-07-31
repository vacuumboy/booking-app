#!/usr/bin/env python3
from __future__ import annotations

import argparse
import os
import sys
import threading
import time
from pathlib import Path

import yaml
from dotenv import load_dotenv

from filters import Candidate, FilterConfig, matches, rank_closest
from notify import fetch_chat_ids, format_closest, format_match, format_status, send_telegram
from sources.collector import collect_raw_listings
from specs_db import SpecsDB
from storage import ListingRecord, ListingStore


ROOT = Path(__file__).resolve().parent
_SCAN_LOCK = threading.Lock()


def load_config(path: Path) -> dict:
    with path.open("r", encoding="utf-8") as handle:
        return yaml.safe_load(handle)


def build_filter_config(raw: dict) -> FilterConfig:
    filters = raw["filters"]
    return FilterConfig(
        max_price_eur=float(filters["max_price_eur"]),
        notify_on_price_drop=bool(filters.get("notify_on_price_drop", True)),
        min_refresh_hz=int(filters.get("min_refresh_hz", 120)),
        min_screen_inch=float(filters.get("min_screen_inch", 14)),
        max_screen_inch=float(filters.get("max_screen_inch", 15.6)),
        max_weight_kg=(
            float(filters["max_weight_kg"])
            if filters.get("max_weight_kg") is not None
            else None
        ),
        require_windows=bool(filters.get("require_windows", True)),
        exclude_brands=list(filters.get("exclude_brands", [])),
        exclude_keywords=list(filters.get("exclude_keywords", [])),
        refresh_keywords=list(filters.get("refresh_keywords", [])),
        unknown_refresh_policy=str(filters.get("unknown_refresh_policy", "reject")),
    )


def collect_candidates(config: dict) -> tuple[list[Candidate], object]:
    specs = SpecsDB()
    candidates: list[Candidate] = []
    items, report = collect_raw_listings(config)
    enriched_hits = 0
    for item in items:
        blob, spec = specs.enrich_text(item.title, item.text_blob)
        if spec is not None:
            enriched_hits += 1
        candidates.append(
            Candidate(
                url=item.url,
                title=item.title,
                price=item.price,
                source=item.source,
                store=item.store,
                text_blob=blob,
                specs_known=spec is not None,
                is_gaming_known=spec.is_gaming if spec else None,
            )
        )
    print(f"База спеков: {specs.count()} кодов, совпадений в прогоне: {enriched_hits}")
    return candidates, report


def run_once(config_path: Path, dry_run: bool = False) -> str:
    if not _SCAN_LOCK.acquire(blocking=False):
        msg = "⏳ Прогон уже идёт — новый не запускаю (чтобы не висеть на Playwright)"
        print(msg, flush=True)
        return msg

    try:
        return _run_once_unlocked(config_path, dry_run=dry_run)
    finally:
        _SCAN_LOCK.release()


def _run_once_unlocked(config_path: Path, dry_run: bool = False) -> str:
    load_dotenv(ROOT / ".env")
    config = load_config(config_path)
    filter_cfg = build_filter_config(config)
    store = ListingStore(ROOT / "data" / "listings.db")

    matched = 0
    notified = 0

    candidates, report = collect_candidates(config)
    for candidate in candidates:
        ok, _reason = matches(candidate, filter_cfg)
        if not ok:
            continue
        matched += 1

        record = ListingRecord(
            url=candidate.url,
            title=candidate.title,
            price=candidate.price,
            source=candidate.source,
            store=candidate.store,
        )
        is_new, price_dropped = store.upsert(record)
        should_notify = is_new or (
            price_dropped and filter_cfg.notify_on_price_drop
        )
        if not should_notify:
            continue

        message = format_match(
            title=candidate.title,
            price=candidate.price,
            url=candidate.url,
            reason="новый" if is_new else "цена упала",
            store=candidate.store,
        )
        print(message)
        print("---")
        if dry_run:
            notified += 1
            continue
        send_telegram(message)
        notified += 1

    closest_top = int(config.get("scan", {}).get("closest_top", 5))
    closest = rank_closest(candidates, filter_cfg, top=closest_top)
    closest_msg = format_closest(
        closest,
        max_price=filter_cfg.max_price_eur,
        min_hz=filter_cfg.min_refresh_hz,
        min_inch=filter_cfg.min_screen_inch,
        max_inch=filter_cfg.max_screen_inch,
    )
    print(closest_msg)
    print("---")

    summary = (
        f"Готово. Карточек: {report.scanned}, подошло: {matched}, "
        f"уведомлений: {notified}\n"
        f"ОК: {', '.join(report.ok_stores) or '—'}\n"
        f"Недоступны: {', '.join(report.fail_stores) or '—'}"
    )
    print(summary)

    if (
        not dry_run
        and os.environ.get("TELEGRAM_BOT_TOKEN")
        and os.environ.get("TELEGRAM_CHAT_ID")
    ):
        notify_status = bool(config.get("scan", {}).get("notify_status", False))
        notify_closest = bool(config.get("scan", {}).get("notify_closest", True))
        try:
            if notify_closest and closest_top > 0:
                send_telegram(closest_msg, disable_preview=True)
            if notify_status:
                send_telegram(
                    format_status(
                        scanned=report.scanned,
                        matched=matched,
                        notified=notified,
                        store_ok=report.ok_stores,
                        store_fail=report.fail_stores,
                    )
                )
        except Exception as exc:  # noqa: BLE001
            print(f"⚠ статус в Telegram не отправился: {exc}")

    return summary


def run_loop(config_path: Path, dry_run: bool = False) -> int:
    load_dotenv(ROOT / ".env")
    while True:
        config = load_config(config_path)
        hours = float(config.get("check_interval_hours", 2))
        seconds = max(60, int(hours * 3600))
        started = time.strftime("%Y-%m-%d %H:%M:%S")
        print(f"\n===== Проверка {started} =====")
        try:
            run_once(config_path, dry_run=dry_run)
        except Exception as exc:  # noqa: BLE001
            print(f"⚠ ошибка прогона: {exc}")
        print(f"Сплю {seconds} сек...")
        time.sleep(seconds)


def run_bot(config_path: Path, dry_run: bool = False) -> int:
    """Interactive Telegram bot + scheduled scans in background."""
    from bot import TelegramBot

    load_dotenv(ROOT / ".env")
    token = os.environ.get("TELEGRAM_BOT_TOKEN", "").strip()
    chat_id = os.environ.get("TELEGRAM_CHAT_ID", "").strip()
    if not token or not chat_id:
        print("Нужны TELEGRAM_BOT_TOKEN и TELEGRAM_CHAT_ID в .env", file=sys.stderr)
        return 1

    stop = threading.Event()

    def scheduled() -> None:
        while not stop.is_set():
            config = load_config(config_path)
            hours = float(config.get("check_interval_hours", 2))
            seconds = max(60, int(hours * 3600))
            started = time.strftime("%Y-%m-%d %H:%M:%S")
            print(f"\n===== Автопроверка {started} =====")
            try:
                run_once(config_path, dry_run=dry_run)
            except Exception as exc:  # noqa: BLE001
                print(f"⚠ ошибка прогона: {exc}")
            stop.wait(seconds)

    threading.Thread(target=scheduled, daemon=True).start()

    def on_scan() -> str:
        return run_once(config_path, dry_run=dry_run)

    bot = TelegramBot(
        token=token,
        allowed_chat_id=chat_id,
        config_path=config_path,
        on_scan=on_scan,
    )
    try:
        bot.run_forever()
    finally:
        stop.set()
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description="Монитор ноутбуков LV + Telegram")
    parser.add_argument("--config", default=str(ROOT / "config.yaml"))
    parser.add_argument("--once", action="store_true")
    parser.add_argument("--loop", action="store_true")
    parser.add_argument(
        "--bot",
        action="store_true",
        help="Telegram-бот с кнопками + автопрогон по расписанию",
    )
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--test-telegram", action="store_true")
    parser.add_argument("--get-chat-id", action="store_true")
    args = parser.parse_args()

    load_dotenv(ROOT / ".env")

    if args.get_chat_id:
        token = os.environ.get("TELEGRAM_BOT_TOKEN", "").strip()
        if not token:
            print("Заполни TELEGRAM_BOT_TOKEN в .env", file=sys.stderr)
            return 1
        chats = fetch_chat_ids(token)
        if not chats:
            print("Пока пусто. Напиши боту /start и запусти снова.")
            return 1
        for chat in chats:
            username = f" @{chat['username']}" if chat.get("username") else ""
            print(
                f"TELEGRAM_CHAT_ID={chat['chat_id']}  "
                f"({chat['name']}{username}, {chat['type']})"
            )
        return 0

    if args.test_telegram:
        send_telegram("✅ Laptop watcher: Telegram работает")
        print("Тестовое сообщение отправлено")
        return 0

    config_path = Path(args.config)
    if not config_path.exists():
        print(
            f"Нет {config_path}. Скопируй config.example.yaml → config.yaml",
            file=sys.stderr,
        )
        return 1

    if args.bot:
        return run_bot(config_path, dry_run=args.dry_run)
    if args.loop:
        return run_loop(config_path, dry_run=args.dry_run)

    run_once(config_path, dry_run=args.dry_run)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
