# Laptop Watcher (Латвия)

Скрипт ищет ноутбуки по твоим фильтрам на Euronics и вручную заданных ссылках Dateks, и шлёт уведомления в Telegram.

## Что нужно от тебя

1. **Telegram-бот**
   - Напиши [@BotFather](https://t.me/BotFather) → `/newbot`
   - Скопируй **токен** (`TELEGRAM_BOT_TOKEN`)

2. **Твой chat id**
   - Напиши боту любое сообщение (например `/start`)
   - Запусти: `python watcher.py --get-chat-id` — скопируй число в `.env` как `TELEGRAM_CHAT_ID`
   - Либо открой **отдельный** чат [@userinfobot](https://t.me/userinfobot) через поиск Telegram (не пиши `@userinfobot` в чат со своим ботом)

3. **Фильтры** — отредактируй `config.yaml` (цена, 120 Hz, вес, бренды)

## Установка

```bash
cd laptop-watcher
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
cp config.example.yaml config.yaml
# заполни .env
```

## Проверка

```bash
python watcher.py --test-telegram
python watcher.py --once --dry-run
python watcher.py --once
```

## Автозапуск каждые 3 часа (cron)

```bash
crontab -e
```

```
0 */3 * * * cd /path/to/laptop-watcher && .venv/bin/python watcher.py --once >> watcher.log 2>&1
```

## Ограничения

- **Dateks** часто блокирует ботов (Cloudflare) — добавляй конкретные openbox-ссылки в `config.yaml → sources.dateks_urls.urls`
- **Tet** пока выключен (тяжёлый фронт)
- Вес/Hz иногда только в названии — для точности добавляй URL Dateks вручную

## Добавить свои ссылки Dateks

В `config.yaml`:

```yaml
sources:
  dateks_urls:
    urls:
      - "https://www.dateks.lv/cenas/portativie-datori/....-openbox"
```
