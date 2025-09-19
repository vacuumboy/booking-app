# 📂 Где хранятся кеши Laravel

## 🗂️ Структура папок кеша

Laravel хранит различные типы кеша в двух основных местах:

### 1. 📁 `bootstrap/cache/` - Кеш загрузки приложения

```
bootstrap/cache/
├── config.php          # Кеш конфигурации (php artisan config:cache)
├── services.php         # Кеш сервисов
├── packages.php         # Кеш пакетов
└── .gitignore
```

**Что хранится:**
- **`config.php`** - Объединенная конфигурация всех конфигурационных файлов
- **`services.php`** - Кеш сервис-провайдеров
- **`packages.php`** - Кеш установленных пакетов

**Когда создается:**
- `php artisan config:cache` - создает config.php
- `php artisan optimize` - создает все файлы

### 2. 📁 `storage/framework/` - Основной кеш приложения

```
storage/framework/
├── cache/
│   ├── data/            # Данные кеша приложения
│   └── .gitignore
├── sessions/            # Файлы сессий (если используется file драйвер)
├── views/              # Скомпилированные Blade шаблоны
└── testing/            # Кеш для тестирования
```

## 📋 Подробное описание каждого типа кеша

### 🔧 **Кеш конфигурации** (`bootstrap/cache/config.php`)
- **Команда создания:** `php artisan config:cache`
- **Команда очистки:** `php artisan config:clear`
- **Размер:** ~21KB (в вашем проекте)
- **Что содержит:** Все настройки из папки `config/`

### 🗃️ **Кеш данных приложения** (`storage/framework/cache/data/`)
- **Команда очистки:** `php artisan cache:clear`
- **Что содержит:** Кешированные данные через `Cache::put()`, `Cache::remember()` и т.д.
- **Структура:** Подпапки по хешам имен ключей

### 🎨 **Кеш представлений** (`storage/framework/views/`)
- **Команда очистки:** `php artisan view:clear`
- **Что содержит:** Скомпилированные PHP файлы из Blade шаблонов
- **Примеры файлов в вашем проекте:**
  - `195081d05c7d363bb269a4d2a2dab389.php` (6.7KB)
  - `3d8097b10658b406e924a6c78207e3c0.php` (28KB)
  - `bf1f8a82a041a2f140d9d027c5d4999d.php` (14KB)

### 🛣️ **Кеш маршрутов** (`bootstrap/cache/routes-*.php`)
- **Команда создания:** `php artisan route:cache`
- **Команда очистки:** `php artisan route:clear`
- **Что содержит:** Скомпилированные маршруты для быстрой загрузки

### 🎭 **Кеш событий** (`bootstrap/cache/events.php`)
- **Команда создания:** `php artisan event:cache`
- **Команда очистки:** `php artisan event:clear`
- **Что содержит:** Карта событий и слушателей

### 📝 **Файлы сессий** (`storage/framework/sessions/`)
- **Очистка:** Через `session:clear` или вручную
- **Что содержит:** Данные пользовательских сессий (если используется `file` драйвер)

## 🔍 Как проверить размер кеша

### Посмотреть размер всех файлов кеша:

```bash
# Размер кеша конфигурации
ls -lh bootstrap/cache/

# Размер кеша представлений
ls -lh storage/framework/views/

# Размер кеша данных
du -sh storage/framework/cache/

# Размер всего кеша
du -sh bootstrap/cache/ storage/framework/cache/ storage/framework/views/
```

### Через PHP скрипт:

```php
<?php
$locations = [
    'bootstrap/cache/' => 'Кеш загрузки',
    'storage/framework/cache/' => 'Кеш данных',
    'storage/framework/views/' => 'Кеш представлений',
    'storage/framework/sessions/' => 'Сессии'
];

foreach ($locations as $path => $description) {
    if (is_dir($path)) {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        echo "$description: " . number_format($size / 1024, 2) . " KB\n";
    }
}
?>
```

## 🧹 Команды для очистки всего кеша

### Очистка всех типов кеша:
```bash
php artisan optimize:clear
```

### Пошаговая очистка:
```bash
php artisan config:clear    # Очистка конфигурации
php artisan cache:clear     # Очистка данных приложения
php artisan route:clear     # Очистка маршрутов
php artisan view:clear      # Очистка представлений
php artisan event:clear     # Очистка событий
```

### Ручная очистка файлов:
```bash
# Удаление всех файлов кеша
rm -rf bootstrap/cache/config.php
rm -rf bootstrap/cache/services.php
rm -rf bootstrap/cache/packages.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*.php
rm -rf storage/framework/sessions/*
```

## ⚠️ Важные моменты

### 🚨 **Не удаляйте папки, только файлы внутри!**
- ✅ Можно: `rm storage/framework/views/*.php`
- ❌ Нельзя: `rm -rf storage/framework/views/`

### 🔒 **Права доступа:**
Убедитесь, что веб-сервер может записывать в эти папки:
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### 🏭 **Продакшн рекомендации:**
```bash
# Для продакшена создайте оптимизированный кеш:
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📊 Текущее состояние вашего кеша

На момент проверки в вашем проекте:

- **Кеш конфигурации:** ✅ Создан (~21KB)
- **Кеш представлений:** ✅ 7 файлов (общий размер ~56KB)
- **Кеш данных:** ✅ Пустой (только .gitignore)
- **Сессии:** ✅ Пустые (только .gitignore)

## 🔄 Автоматическое управление кешем

Используйте созданные ранее скрипты для автоматизации:

- **Веб-интерфейс:** `http://ваш-сайт.com/clear-cache.php`
- **Быстрая очистка:** `http://ваш-сайт.com/quick-clear.php`
- **Консольные скрипты:** `php clear_cache.php`

Эти скрипты автоматически очищают все указанные выше места хранения кеша! 