#!/usr/bin/env python3
from __future__ import annotations

import argparse
import os
import sys
from pathlib import Path

import yaml
from dotenv import load_dotenv

from filters import Candidate, FilterConfig, matches
from notify import fetch_chat_ids, format_match, send_telegram
from sources.collector import collect_raw_listings
from storage import ListingRecord, ListingStore


ROOT = Path(__file__).resolve().parent


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
        max_weight_kg=float(filters.get("max_weight_kg", 1.55)),
        require_windows=bool(filters.get("require_windows", True)),
        exclude_brands=list(filters.get("exclude_brands", [])),
        exclude_keywords=list(filters.get("exclude_keywords", [])),
        refresh_keywords=list(filters.get("refresh_keywords", [])),
    )


def collect_candidates(config: dict) -> list[Candidate]:
    candidates: list[Candidate] = []

    for item in collect_raw_listings(config):
        candidates.append(
            Candidate(
                url=item.url,
                title=item.title,
                price=item.price,
                source=item.source,
                store=item.store,
                text_blob=item.text_blob,
            )
        )

    return candidates


def run_once(config_path: Path, dry_run: bool = False) -> int:
    load_dotenv(ROOT / ".env")
    config = load_config(config_path)
    filter_cfg = build_filter_config(config)
    store = ListingStore(ROOT / "data" / "listings.db")

    matched = 0
    notified = 0

    for candidate in collect_candidates(config):
        ok, reason = matches(candidate, filter_cfg)
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

    print(f"Готово. Подошло: {matched}, уведомлений: {notified}")
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description="Монитор ноутбуков LV + Telegram")
    parser.add_argument(
        "--config",
        default=str(ROOT / "config.yaml"),
        help="Путь к config.yaml",
    )
    parser.add_argument(
        "--once",
        action="store_true",
        help="Один проход и выход",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Не слать в Telegram, только печать в консоль",
    )
    parser.add_argument(
        "--test-telegram",
        action="store_true",
        help="Проверить Telegram-уведомление",
    )
    parser.add_argument(
        "--get-chat-id",
        action="store_true",
        help="Показать chat id из сообщений, которые ты уже написал боту",
    )
    args = parser.parse_args()

    load_dotenv(ROOT / ".env")

    if args.get_chat_id:
        token = os.environ.get("TELEGRAM_BOT_TOKEN", "").strip()
        if not token:
            print("Заполни TELEGRAM_BOT_TOKEN в .env", file=sys.stderr)
            return 1
        chats = fetch_chat_ids(token)
        if not chats:
            print(
                "Пока пусто. Напиши боту «Noutbuk» любое сообщение "
                "(например /start) и запусти снова:\n"
                "  python watcher.py --get-chat-id"
            )
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

    if args.once or True:
        return run_once(config_path, dry_run=args.dry_run)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
