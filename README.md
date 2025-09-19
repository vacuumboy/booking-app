# Booking App

Приложение для управления салоном красоты с адаптивным дизайном, который сохраняет визуальную идентичность на всех устройствах.

## Адаптивная система дизайна

### Основные принципы

1. **Визуальная идентичность** - элементы сохраняют свой цвет, стиль и порядок на всех устройствах
2. **Технические единицы** - используются относительные единицы (rem, em, %, vw/vh) вместо фиксированных (px)
3. **Гибкие сетки** - адаптивные Grid и Flexbox системы
4. **Медиазапросы** - точки останова основаны на rem для масштабируемости

### Точки останова (Breakpoints)

```css
/* Мобильные устройства */
@media screen and (max-width: 48rem) { /* 768px */ }

/* Планшеты */
@media screen and (min-width: 48rem) and (max-width: 64rem) { /* 768px - 1024px */ }

/* Десктопы */
@media screen and (min-width: 64rem) { /* 1024px+ */ }

/* Крупные экраны */
@media screen and (min-width: 90rem) { /* 1440px+ */ }
```

### CSS-переменные

#### Размеры шрифтов
```css
--font-size-xs: 0.75rem;
--font-size-sm: 0.875rem;
--font-size-base: 1rem;
--font-size-lg: 1.125rem;
--font-size-xl: 1.25rem;
--font-size-2xl: 1.5rem;
--font-size-3xl: 1.875rem;
--font-size-4xl: 2.25rem;
```

#### Отступы
```css
--spacing-xs: 0.25rem;
--spacing-sm: 0.5rem;
--spacing-base: 1rem;
--spacing-lg: 1.5rem;
--spacing-xl: 2rem;
--spacing-2xl: 3rem;
--spacing-3xl: 4rem;
```

#### Цвета
```css
--color-primary: #3b82f6;
--color-primary-hover: #2563eb;
--color-background: #f9fafb;
--color-surface: #ffffff;
--color-text: #1f2937;
--color-text-secondary: #6b7280;
--color-border: #e5e7eb;
```

### Адаптивные классы

#### Контейнеры
```html
<div class="container-adaptive">
  <!-- Адаптивный контейнер с максимальной шириной -->
</div>
```

#### Сетки
```html
<div class="grid-adaptive">
  <!-- Адаптивная сетка: 1 колонка на мобильных, 2 на планшетах, 4 на десктопах -->
</div>
```

#### Кнопки
```html
<button class="btn-adaptive bg-adaptive-primary text-white">
  Адаптивная кнопка
</button>
```

#### Карточки
```html
<div class="card-adaptive">
  <!-- Адаптивная карточка с тенями и hover эффектами -->
</div>

<!-- Карточки с равной высотой -->
<div class="dashboard-info-cards">
  <div class="adaptive-card">
    <div class="card-content">
      <!-- Основное содержимое -->
    </div>
    <div class="card-footer">
      <!-- Подвал карточки -->
    </div>
  </div>
</div>

<!-- Альтернативный способ для равной высоты -->
<div class="adaptive-cards-equal-height">
  <div class="adaptive-card">
    <!-- Содержимое карточки -->
  </div>
</div>
```

#### Формы
```html
<form class="form-adaptive">
  <div class="adaptive-form-group">
    <label class="adaptive-form-label">Метка</label>
    <input class="input-adaptive" type="text" placeholder="Ввод">
  </div>
</form>
```

#### Типографика
```html
<h1 class="adaptive-heading-1">Заголовок 1</h1>
<h2 class="adaptive-heading-2">Заголовок 2</h2>
<h3 class="adaptive-heading-3">Заголовок 3</h3>
<p class="adaptive-text-body">Основной текст</p>
<small class="adaptive-text-small">Мелкий текст</small>
```

#### Скрытие/показ элементов
```html
<div class="hide-mobile">Скрыто на мобильных</div>
<div class="show-mobile">Показано только на мобильных</div>
<div class="hide-tablet">Скрыто на планшетах</div>
<div class="show-tablet">Показано только на планшетах</div>
<div class="hide-desktop">Скрыто на десктопах</div>
<div class="show-desktop">Показано только на десктопах</div>
```

### Tailwind CSS интеграция

#### Адаптивные размеры шрифтов
```html
<h1 class="text-adaptive-4xl">Большой заголовок</h1>
<p class="text-adaptive-base">Основной текст</p>
```

#### Адаптивные отступы
```html
<div class="p-adaptive-lg m-adaptive-base">
  <!-- Адаптивные отступы -->
</div>
```

#### Адаптивные цвета
```html
<div class="bg-adaptive-primary text-white">
  <!-- Адаптивные цвета -->
</div>
```

### Использование в компонентах

#### Адаптивная навигация
```html
<nav class="adaptive-nav">
  <div class="adaptive-nav-content">
    <a href="#" class="adaptive-nav-brand">Логотип</a>
    <div class="adaptive-nav-links hide-mobile">
      <a href="#" class="adaptive-nav-link">Ссылка</a>
    </div>
  </div>
</nav>
```

#### Адаптивные таблицы
```html
<div class="adaptive-table-container">
  <table class="adaptive-table">
    <thead>
      <tr>
        <th>Заголовок</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Данные</td>
      </tr>
    </tbody>
  </table>
</div>
```

#### Адаптивные модальные окна
```html
<div class="adaptive-modal">
  <div class="adaptive-modal-content">
    <div class="adaptive-modal-header">
      <h2 class="adaptive-modal-title">Заголовок</h2>
    </div>
    <div class="adaptive-modal-body">
      Содержимое модального окна
    </div>
    <div class="adaptive-modal-footer">
      <button class="btn-adaptive">Закрыть</button>
    </div>
  </div>
</div>
```

### Календарь

#### Адаптивный календарь
```html
<div class="adaptive-calendar">
  <div class="adaptive-calendar-grid">
    <div class="adaptive-calendar-time">09:00</div>
    <div class="adaptive-calendar-cell">
      <div class="adaptive-calendar-appointment" style="background: #3b82f6">
        Запись клиента
      </div>
    </div>
  </div>
</div>
```

### Особенности мобильной версии

1. **Предотвращение зума на iOS** - размер шрифта в input не менее 1rem
2. **Touch-friendly интерфейс** - минимальная область касания 2.75rem
3. **Оптимизация прокрутки** - `-webkit-overflow-scrolling: touch`
4. **Отключение outline** - для лучшего UX на touch устройствах

### Производительность

1. **CSS-переменные** - динамическое изменение значений без перекомпиляции
2. **Оптимизированные медиазапросы** - минимальное количество точек останова
3. **Легкие тени и анимации** - плавные переходы без влияния на производительность
4. **Кэширование** - переменные кэшируются браузером

### Тестирование адаптивности

1. **Проверка на разных устройствах**
2. **Тестирование масштабирования браузера**
3. **Проверка в режиме разработчика**
4. **Тестирование на реальных устройствах**

### Примеры использования

Смотрите файл `resources/views/dashboard.blade.php` для примера полной интеграции адаптивной системы.

## Установка

```bash
# Установка зависимостей
composer install
npm install

# Настройка окружения
cp .env.example .env
php artisan key:generate

# Миграции
php artisan migrate

# Сборка ресурсов
npm run dev
```

## Запуск

```bash
# Локальный сервер
php artisan serve

# Автоматическая пересборка CSS/JS
npm run watch
```

## Поддержка браузеров

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- iOS Safari 12+
- Android Chrome 60+

### Исправления и улучшения

#### Выравнивание карточек
- **Проблема**: Карточки в сетке имели разную высоту на десктопе
- **Решение**: Добавлены flexbox свойства и `align-items: stretch` для равной высоты
- **Классы**: 
  - `dashboard-info-cards` - специальный класс для информационных карточек
  - `adaptive-cards-equal-height` - универсальный класс для равной высоты
  - `card-content` и `card-footer` - для структурирования содержимого карточки

#### Структура карточки
```html
<div class="adaptive-card">
  <div class="card-content">
    <!-- Основное содержимое, которое растягивается -->
  </div>
  <div class="card-footer">
    <!-- Подвал карточки, прижимается к низу -->
  </div>
</div>
```

### Особенности

- Поддержка RTL (готовность к добавлению)
- Темная тема (готовность к добавлению)
- Высокая контрастность
- Оптимизация для печати
- SEO-оптимизация
- **Равная высота карточек** - автоматическое выравнивание
- **Flexbox структура** - гибкое распределение содержимого

## Лицензия

MIT License
