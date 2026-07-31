from __future__ import annotations

import os

import httpx


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


def send_telegram(message: str) -> None:
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
                "disable_web_page_preview": False,
            },
        )
        response.raise_for_status()


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
