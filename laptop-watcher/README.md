# Laptop Watcher (Латвия)

Скрипт ищет ноутбуки по фильтрам в латвийских магазинах с рассрочкой и шлёт уведомления в Telegram.

## Магазины (с рассрочкой)

| Магазин | Nomaksa / līzings |
|---------|-------------------|
| **Euronics** | Inbank līdz 60 mēn. |
| **Tet** | līdz 36 mēn. |
| **220.lv** | 0% nomaksa |
| **Dateks** | Inbank līdz 48 mēn. |
| **RD Electronics** | līdz 60 mēn. |
| **1a.lv** | Esto / Inbank |
| **AiO.lv** | Klix / Esto |
| **Elkor** | nomaksa |
| **Signe, Kursi, Bigbox, Baltic Data** | nomaksa |

**Сейчас реально сканируются:** Euronics, Tet, Dateks, AiO.lv, Baltic Data, **M79.lv**.  
Playwright обходит Cloudflare для Dateks / AiO / Baltic Data. M79 открывается обычным HTTP.  
220 / RD / 1a / Elkor / Bigbox пока выключены (Cloudflare / нет нормального каталога).

Включение/выключение — в `config.yaml → sources`.

## Что нужно от тебя

1. **Telegram-бот**
   - Напиши [@BotFather](https://t.me/BotFather) → `/newbot`
   - Скопируй **токен** (`TELEGRAM_BOT_TOKEN`)

2. **Твой chat id**
   - Напиши боту любое сообщение (например `/start`)
   - Запусти: `python watcher.py --get-chat-id` — скопируй число в `.env` как `TELEGRAM_CHAT_ID`
   - Либо открой **отдельный** чат [@userinfobot](https://t.me/userinfobot) через поиск Telegram (не пиши `@userinfobot` в чат со своим ботом)

3. **Фильтры** — в `config.yaml` или кнопками в боте (⚙️ Настройки)

## База спецификаций (коды моделей)

В карточках магазинов часто **нет 120 Hz**. Поэтому есть база `specs/seed.yaml` → `data/specs.db`:
- ключ = MPN / код модели / алиас (`M5406WA`, `UX3405`, `Pavilion Plus 14`…)
- поля: `refresh_hz`, диагональ, вес, `is_gaming`

При скане скрипт ищет код в названии/описании и **дописывает спеки**. Пополняй `specs/seed.yaml`.

`unknown_refresh_policy`:
- `reject` — нет Hz ни в карточке, ни в базе → мимо
- `accept` — нет Hz → всё равно рассматриваем (риск ложных)

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

## Проверка / бот с кнопками

```bash
python watcher.py --test-telegram
python watcher.py --once --dry-run

# Рекомендуется: бот + автопрогон
python watcher.py --bot
```

В Telegram:
- **🔍 Прогон** — ручной полный скан
- **⚙️ Настройки** — цена, Hz, политика «Hz неизвестна»
- **📊 Статус**
- или `/set max_price_eur 1000`

Лимиты скана по умолчанию **сняты** (`max_pages/max_enrich: null`).

## Автозапуск на GitHub (рекомендуется)

Скрипт уже настроен в `.github/workflows/laptop-watcher.yml` — проверка **каждые 2 часа**.

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

- **Playwright** для Dateks + AiO.lv (проверено) и 220.lv (часто всё равно challenge)
- Остальные магазины (RD, 1a…) пока без браузера — при блоке пропускаются
- Сканирование ограничено `scan.max_enrich_per_source` (для browser-магазинов ≤25 карточек)
- Вес/Hz иногда только на странице товара — приоритет у моделей 14–15″ и с «120 Hz» в названии

Локально без браузера:
```bash
export LAPTOP_WATCHER_DISABLE_BROWSER=1
```

## Добавить свои ссылки Dateks

В `config.yaml`:

```yaml
sources:
  dateks_urls:
    urls:
      - "https://www.dateks.lv/cenas/portativie-datori/....-openbox"
```
