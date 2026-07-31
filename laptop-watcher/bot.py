from __future__ import annotations

import json
import os
import threading
import time
from pathlib import Path
from typing import Any, Callable

import httpx
import yaml


ROOT = Path(__file__).resolve().parent


class TelegramBot:
    """Long-polling bot with scan button and editable settings."""

    def __init__(
        self,
        token: str,
        allowed_chat_id: str,
        config_path: Path,
        on_scan: Callable[[], str],
    ) -> None:
        self.token = token
        self.allowed_chat_id = str(allowed_chat_id)
        self.config_path = config_path
        self.on_scan = on_scan
        self.api = f"https://api.telegram.org/bot{token}"
        self.offset = 0
        self._scan_lock = threading.Lock()
        self._awaiting: dict[str, str] = {}  # chat_id -> field name

    def run_forever(self) -> None:
        self._send(
            self.allowed_chat_id,
            "🤖 Бот монитора запущен.\nНажми кнопки ниже или /start",
            reply_markup=self._main_keyboard(),
        )
        print("Telegram bot: polling…")
        while True:
            try:
                updates = self._get_updates()
                for update in updates:
                    self.offset = update["update_id"] + 1
                    self._handle_update(update)
            except Exception as exc:  # noqa: BLE001
                print(f"⚠ bot poll error: {exc}")
                time.sleep(3)

    def _get_updates(self) -> list[dict]:
        with httpx.Client(timeout=60) as client:
            response = client.get(
                f"{self.api}/getUpdates",
                params={
                    "timeout": 50,
                    "offset": self.offset,
                    "allowed_updates": json.dumps(["message", "callback_query"]),
                },
            )
            response.raise_for_status()
            payload = response.json()
        if not payload.get("ok"):
            return []
        return list(payload.get("result", []))

    def _handle_update(self, update: dict) -> None:
        if "callback_query" in update:
            self._handle_callback(update["callback_query"])
            return
        message = update.get("message") or {}
        chat = message.get("chat") or {}
        chat_id = str(chat.get("id", ""))
        if chat_id != self.allowed_chat_id:
            return
        text = (message.get("text") or "").strip()
        if not text:
            return

        awaiting = self._awaiting.pop(chat_id, None)
        if awaiting:
            self._apply_setting(chat_id, awaiting, text)
            return

        if text in {"/start", "/menu", "меню", "Меню"}:
            self._send(
                chat_id,
                "Меню монитора ноутбуков:",
                reply_markup=self._main_keyboard(),
            )
            return
        if text in {"/scan", "🔍 Прогон", "Прогон"}:
            self._run_scan(chat_id)
            return
        if text in {"/settings", "⚙️ Настройки", "Настройки"}:
            self._send_settings(chat_id)
            return
        if text in {"/status", "📊 Статус", "Статус"}:
            self._send_status(chat_id)
            return
        if text.startswith("/set "):
            # /set max_price_eur 1000
            parts = text.split(maxsplit=2)
            if len(parts) == 3:
                self._apply_setting(chat_id, parts[1], parts[2])
            else:
                self._send(chat_id, "Формат: /set max_price_eur 1000")
            return

        self._send(
            chat_id,
            "Не понял. Жми кнопки или /start",
            reply_markup=self._main_keyboard(),
        )

    def _handle_callback(self, query: dict) -> None:
        chat = (query.get("message") or {}).get("chat") or {}
        chat_id = str(chat.get("id", ""))
        data = query.get("data") or ""
        query_id = query.get("id")
        if chat_id != self.allowed_chat_id:
            return
        self._answer_callback(query_id)

        if data == "scan":
            self._run_scan(chat_id)
        elif data == "settings":
            self._send_settings(chat_id)
        elif data == "status":
            self._send_status(chat_id)
        elif data.startswith("edit:"):
            field = data.split(":", 1)[1]
            self._awaiting[chat_id] = field
            self._send(
                chat_id,
                f"Введи новое значение для `{field}`:\n"
                f"(сейчас: {self._get_filter_value(field)})",
            )
        elif data.startswith("toggle_store:"):
            store_id = data.split(":", 1)[1]
            self._toggle_store(chat_id, store_id)

    def _run_scan(self, chat_id: str) -> None:
        if not self._scan_lock.acquire(blocking=False):
            self._send(chat_id, "⏳ Прогон уже идёт, подожди…")
            return

        def worker() -> None:
            try:
                self._send(chat_id, "🔍 Запускаю полный прогон… это может занять несколько минут")
                summary = self.on_scan()
                self._send(chat_id, summary, reply_markup=self._main_keyboard())
            except Exception as exc:  # noqa: BLE001
                self._send(chat_id, f"⚠ Ошибка прогона: {exc}")
            finally:
                self._scan_lock.release()

        threading.Thread(target=worker, daemon=True).start()

    def _send_settings(self, chat_id: str) -> None:
        cfg = self._load_config()
        filters = cfg.get("filters", {})
        text = (
            "⚙️ Текущие фильтры\n\n"
            f"• max_price_eur = {filters.get('max_price_eur')}\n"
            f"• min_refresh_hz = {filters.get('min_refresh_hz')}\n"
            f"• screen = {filters.get('min_screen_inch')}–{filters.get('max_screen_inch')}\"\n"
            f"• unknown_refresh_policy = {filters.get('unknown_refresh_policy', 'reject')}\n"
            f"• exclude_brands = {', '.join(filters.get('exclude_brands') or [])}\n\n"
            "Нажми поле чтобы изменить, или:\n"
            "`/set max_price_eur 1000`\n"
            "`/set min_refresh_hz 120`\n"
            "`/set unknown_refresh_policy accept`\n"
            "(accept = пропускать если Hz неизвестна)"
        )
        keyboard = {
            "inline_keyboard": [
                [
                    {"text": "💰 Цена", "callback_data": "edit:max_price_eur"},
                    {"text": "Hz", "callback_data": "edit:min_refresh_hz"},
                ],
                [
                    {
                        "text": "Hz неизвестна: reject/accept",
                        "callback_data": "edit:unknown_refresh_policy",
                    }
                ],
                [
                    {"text": "Диаг. min", "callback_data": "edit:min_screen_inch"},
                    {"text": "Диаг. max", "callback_data": "edit:max_screen_inch"},
                ],
                [{"text": "🔙 Меню", "callback_data": "status"}],
            ]
        }
        self._send(chat_id, text, reply_markup=keyboard)

    def _send_status(self, chat_id: str) -> None:
        cfg = self._load_config()
        filters = cfg.get("filters", {})
        sources = cfg.get("sources", {})
        enabled = [
            key
            for key, value in sources.items()
            if isinstance(value, dict) and value.get("enabled") and key != "dateks_urls"
        ]
        text = (
            "📊 Статус\n\n"
            f"Интервал: {cfg.get('check_interval_hours')} ч\n"
            f"Цена ≤ {filters.get('max_price_eur')} €\n"
            f"Hz ≥ {filters.get('min_refresh_hz')}\n"
            f"Магазины: {', '.join(enabled) or '—'}\n\n"
            "Кнопки ниже — действия"
        )
        self._send(chat_id, text, reply_markup=self._main_keyboard())

    def _apply_setting(self, chat_id: str, field: str, raw_value: str) -> None:
        cfg = self._load_config()
        filters = cfg.setdefault("filters", {})
        value: Any = raw_value.strip()
        if field in {"max_price_eur", "min_screen_inch", "max_screen_inch", "max_weight_kg"}:
            try:
                value = float(value)
            except ValueError:
                self._send(chat_id, "Нужно число, например 900")
                return
        elif field == "min_refresh_hz":
            try:
                value = int(float(value))
            except ValueError:
                self._send(chat_id, "Нужно целое число, например 120")
                return
        elif field == "unknown_refresh_policy":
            value = value.lower()
            if value not in {"reject", "accept"}:
                self._send(chat_id, "Допустимо: reject или accept")
                return
        elif field == "exclude_brands":
            value = [part.strip() for part in value.split(",") if part.strip()]
        else:
            self._send(chat_id, f"Неизвестное поле: {field}")
            return

        filters[field] = value
        self._save_config(cfg)
        self._send(
            chat_id,
            f"✅ Сохранено: {field} = {value}",
            reply_markup=self._main_keyboard(),
        )

    def _toggle_store(self, chat_id: str, store_id: str) -> None:
        cfg = self._load_config()
        store = cfg.setdefault("sources", {}).setdefault(store_id, {})
        store["enabled"] = not bool(store.get("enabled", False))
        self._save_config(cfg)
        state = "вкл" if store["enabled"] else "выкл"
        self._send(chat_id, f"Магазин {store_id}: {state}")

    def _get_filter_value(self, field: str) -> Any:
        return self._load_config().get("filters", {}).get(field, "—")

    def _load_config(self) -> dict:
        with self.config_path.open("r", encoding="utf-8") as handle:
            return yaml.safe_load(handle)

    def _save_config(self, cfg: dict) -> None:
        with self.config_path.open("w", encoding="utf-8") as handle:
            yaml.safe_dump(cfg, handle, allow_unicode=True, sort_keys=False)

    def _main_keyboard(self) -> dict:
        return {
            "keyboard": [
                [{"text": "🔍 Прогон"}, {"text": "⚙️ Настройки"}],
                [{"text": "📊 Статус"}],
            ],
            "resize_keyboard": True,
        }

    def _send(
        self,
        chat_id: str,
        text: str,
        reply_markup: dict | None = None,
    ) -> None:
        payload: dict[str, Any] = {
            "chat_id": chat_id,
            "text": text,
            "disable_web_page_preview": True,
        }
        if reply_markup is not None:
            payload["reply_markup"] = reply_markup
        with httpx.Client(timeout=30) as client:
            client.post(f"{self.api}/sendMessage", json=payload).raise_for_status()

    def _answer_callback(self, query_id: str | None) -> None:
        if not query_id:
            return
        with httpx.Client(timeout=15) as client:
            client.post(
                f"{self.api}/answerCallbackQuery",
                json={"callback_query_id": query_id},
            )
