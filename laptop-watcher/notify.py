from __future__ import annotations

import os
import threading
import time
from typing import Callable

import httpx


ProgressCallback = Callable[[str], None]


def fetch_chat_ids(token: str) -> list[dict]:
    """Возвращает chat id из последних сообщений боту (getUpdates)."""
    url = f"https://api.telegram.org/bot{token}/getUpdates"
    with httpx.Client(timeout=30) as client:
        response = client.get(url)
        response.raise_for_status()
        payload = response.json()

    if not payload.get("ok"):
        raise RuntimeError(f"Telegram API error: {payload}")

    seen: dict[int, dict] = {}
    for update in payload.get("result", []):
        message = update.get("message") or update.get("edited_message")
        if not message:
            continue
        chat = message.get("chat", {})
        chat_id = chat.get("id")
        if chat_id is None:
            continue
        seen[int(chat_id)] = {
            "chat_id": int(chat_id),
            "type": chat.get("type"),
            "name": chat.get("first_name") or chat.get("title") or "?",
            "username": chat.get("username"),
            "last_text": message.get("text", ""),
        }
    return list(seen.values())


def send_telegram(message: str, *, disable_preview: bool = False) -> None:
    token = os.environ.get("TELEGRAM_BOT_TOKEN", "").strip()
    chat_id = os.environ.get("TELEGRAM_CHAT_ID", "").strip()
    if not token or not chat_id:
        raise RuntimeError(
            "Заполни TELEGRAM_BOT_TOKEN и TELEGRAM_CHAT_ID в файле .env"
        )

    url = f"https://api.telegram.org/bot{token}/sendMessage"
    with httpx.Client(timeout=30) as client:
        response = client.post(
            url,
            json={
                "chat_id": chat_id,
                "text": message,
                "disable_web_page_preview": disable_preview,
            },
        )
        response.raise_for_status()


def try_send_telegram(message: str) -> None:
    """Best-effort Telegram send — never crash the scanner."""
    try:
        send_telegram(message, disable_preview=True)
    except Exception as exc:  # noqa: BLE001
        print(f"⚠ telegram: {exc}", flush=True)


class Heartbeat:
    """Send Telegram pulse every N checked laptops."""

    def __init__(self, every: int = 5, enabled: bool = True) -> None:
        self.every = max(1, every)
        self.enabled = enabled
        self.checked = 0
        self.ok = 0
        self.fail = 0
        self.store = ""
        self._lock = threading.Lock()
        self.started = time.time()

    def set_store(self, name: str) -> None:
        with self._lock:
            self.store = name
        if self.enabled:
            try_send_telegram(f"🏪 Начинаю: {name}")

    def tick(self, *, success: bool = True, detail: str = "") -> None:
        with self._lock:
            self.checked += 1
            if success:
                self.ok += 1
            else:
                self.fail += 1
            checked = self.checked
            store = self.store
            ok = self.ok
            fail = self.fail
            elapsed = int(time.time() - self.started)

        if not self.enabled:
            return
        if checked % self.every != 0:
            return

        extra = f"\n{detail}" if detail else ""
        try_send_telegram(
            f"💓 Жив. Проверено {checked} ноутов "
            f"(ок {ok}, ошибки {fail})\n"
            f"Сейчас: {store or '—'}\n"
            f"⏱ {elapsed // 60}м {elapsed % 60}с{extra}"
        )

    def alert(self, message: str) -> None:
        """Always send (abort / store skip) — not gated by every-N."""
        if self.enabled:
            try_send_telegram(message)

    def done_store(self, name: str, found: int) -> None:
        if self.enabled:
            try_send_telegram(f"✓ {name}: готово, карточек {found}")


def format_match(
    title: str,
    price: float | None,
    url: str,
    reason: str,
    store: str = "",
) -> str:
    price_line = f"💰 {price:.0f} €" if price is not None else "💰 цена неизвестна"
    store_line = f"🏪 {store}\n" if store else ""
    return (
        "🖥 Нашёл вариант под твои фильтры\n\n"
        f"{store_line}"
        f"{title}\n"
        f"{price_line}\n"
        f"🔗 {url}\n\n"
        f"({reason})"
    )


def format_status(
    scanned: int,
    matched: int,
    notified: int,
    store_ok: list[str],
    store_fail: list[str],
) -> str:
    ok = ", ".join(store_ok) if store_ok else "—"
    fail = ", ".join(store_fail) if store_fail else "—"
    return (
        "✅ Laptop watcher: проверка завершена\n\n"
        f"Проверено карточек: {scanned}\n"
        f"Подошло: {matched}\n"
        f"Уведомлений: {notified}\n\n"
        f"ОК: {ok}\n"
        f"Недоступны: {fail}"
    )
