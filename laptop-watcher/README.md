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

## Автозапуск на GitHub (рекомендуется)

Скрипт уже настроен в `.github/workflows/laptop-watcher.yml` — проверка **каждые 3 часа**.

### Один раз настроить секреты

1. Открой репозиторий на GitHub → **Settings** → **Secrets and variables** → **Actions**
2. **New repository secret**:
   - `TELEGRAM_BOT_TOKEN` — токен от @BotFather
   - `TELEGRAM_CHAT_ID` — твой id (например `830418096`)

### Проверить вручную

**Actions** → **Laptop Watcher** → **Run workflow** → **Run workflow**

Если секреты верные — при нахождении варианта придёт сообщение в Telegram.

> База «уже видели» (`data/listings.db`) хранится в cache GitHub между запусками, чтобы не слать одно и то же каждый раз.

## Автозапуск на своём ПК (cron)

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
