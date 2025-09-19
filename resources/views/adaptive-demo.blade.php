<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Демонстрация адаптивной системы</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-adaptive-background">
        <!-- Адаптивная навигация -->
        <nav class="adaptive-nav">
            <div class="adaptive-nav-content">
                <a href="#" class="adaptive-nav-brand">Адаптивная система</a>
                <div class="adaptive-nav-links">
                    <a href="#typography" class="adaptive-nav-link">Типографика</a>
                    <a href="#components" class="adaptive-nav-link">Компоненты</a>
                    <a href="#forms" class="adaptive-nav-link">Формы</a>
                    <a href="#grid" class="adaptive-nav-link">Сетки</a>
                </div>
            </div>
        </nav>

        <!-- Основной контент -->
        <main class="py-adaptive-xl">
            <div class="container-adaptive">
                <!-- Заголовок -->
                <div class="text-center mb-adaptive-2xl">
                    <h1 class="adaptive-heading-1 text-adaptive-primary">
                        Демонстрация адаптивной системы дизайна
                    </h1>
                    <p class="adaptive-text-body text-adaptive-text-secondary max-w-3xl mx-auto">
                        Этот сайт демонстрирует работу адаптивной системы дизайна, которая сохраняет 
                        визуальную идентичность на всех устройствах, используя относительные единицы 
                        измерения и гибкие сетки.
                    </p>
                </div>

                <!-- Типографика -->
                <section id="typography" class="mb-adaptive-2xl">
                    <div class="card-adaptive">
                        <h2 class="adaptive-heading-2 mb-adaptive-lg">Типографика</h2>
                        <div class="space-y-adaptive-base">
                            <div>
                                <h1 class="adaptive-heading-1">Заголовок 1 (H1)</h1>
                                <p class="adaptive-text-small text-adaptive-text-secondary">
                                    Размер: var(--font-size-4xl), адаптивный на всех устройствах
                                </p>
                            </div>
                            <div>
                                <h2 class="adaptive-heading-2">Заголовок 2 (H2)</h2>
                                <p class="adaptive-text-small text-adaptive-text-secondary">
                                    Размер: var(--font-size-3xl), адаптивный на всех устройствах
                                </p>
                            </div>
                            <div>
                                <h3 class="adaptive-heading-3">Заголовок 3 (H3)</h3>
                                <p class="adaptive-text-small text-adaptive-text-secondary">
                                    Размер: var(--font-size-2xl), адаптивный на всех устройствах
                                </p>
                            </div>
                            <div>
                                <h4 class="adaptive-heading-4">Заголовок 4 (H4)</h4>
                                <p class="adaptive-text-small text-adaptive-text-secondary">
                                    Размер: var(--font-size-xl), адаптивный на всех устройствах
                                </p>
                            </div>
                            <div>
                                <p class="adaptive-text-body">
                                    Основной текст параграфа. Размер: var(--font-size-base). 
                                    Этот текст адаптивно масштабируется на всех устройствах, 
                                    сохраняя читабельность и визуальную иерархию.
                                </p>
                            </div>
                            <div>
                                <p class="adaptive-text-small">
                                    Мелкий текст. Размер: var(--font-size-sm). 
                                    Используется для вспомогательной информации.
                                </p>
                            </div>
                            <div>
                                <p class="adaptive-text-xs">
                                    Очень мелкий текст. Размер: var(--font-size-xs). 
                                    Используется для подписей и пометок.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Кнопки -->
                <section id="components" class="mb-adaptive-2xl">
                    <div class="card-adaptive">
                        <h2 class="adaptive-heading-2 mb-adaptive-lg">Компоненты</h2>
                        
                        <div class="space-y-adaptive-lg">
                            <div>
                                <h3 class="adaptive-heading-3 mb-adaptive-base">Кнопки</h3>
                                <div class="flex-adaptive gap-adaptive-base">
                                    <button class="btn-adaptive bg-adaptive-primary text-white hover:bg-adaptive-primary-hover">
                                        Основная кнопка
                                    </button>
                                    <button class="btn-adaptive bg-adaptive-secondary text-white">
                                        Вторичная кнопка
                                    </button>
                                    <button class="btn-adaptive bg-adaptive-success text-white">
                                        Успех
                                    </button>
                                    <button class="btn-adaptive bg-adaptive-warning text-white">
                                        Предупреждение
                                    </button>
                                    <button class="btn-adaptive bg-adaptive-danger text-white">
                                        Опасность
                                    </button>
                                </div>
                            </div>

                            <div>
                                <h3 class="adaptive-heading-3 mb-adaptive-base">Карточки</h3>
                                <div class="grid-adaptive gap-adaptive-base lg:grid-cols-3">
                                    <div class="card-adaptive">
                                        <h4 class="adaptive-heading-4 mb-adaptive-sm">Карточка 1</h4>
                                        <p class="adaptive-text-body">
                                            Содержимое первой карточки. Эта карточка адаптивно 
                                            изменяет размер в зависимости от устройства.
                                        </p>
                                    </div>
                                    <div class="card-adaptive">
                                        <h4 class="adaptive-heading-4 mb-adaptive-sm">Карточка 2</h4>
                                        <p class="adaptive-text-body">
                                            Содержимое второй карточки. Все карточки сохраняют 
                                            одинаковый стиль на всех устройствах.
                                        </p>
                                    </div>
                                    <div class="card-adaptive">
                                        <h4 class="adaptive-heading-4 mb-adaptive-sm">Карточка 3</h4>
                                        <p class="adaptive-text-body">
                                            Содержимое третьей карточки. Визуальная идентичность 
                                            сохраняется на всех разрешениях.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Формы -->
                <section id="forms" class="mb-adaptive-2xl">
                    <div class="card-adaptive">
                        <h2 class="adaptive-heading-2 mb-adaptive-lg">Формы</h2>
                        
                        <form class="form-adaptive">
                            <div class="adaptive-form-grid">
                                <div class="adaptive-form-group">
                                    <label class="adaptive-form-label">Имя</label>
                                    <input class="input-adaptive" type="text" placeholder="Введите ваше имя">
                                </div>
                                <div class="adaptive-form-group">
                                    <label class="adaptive-form-label">Email</label>
                                    <input class="input-adaptive" type="email" placeholder="example@email.com">
                                </div>
                                <div class="adaptive-form-group">
                                    <label class="adaptive-form-label">Телефон</label>
                                    <input class="input-adaptive" type="tel" placeholder="+7 (999) 123-45-67">
                                </div>
                                <div class="adaptive-form-group">
                                    <label class="adaptive-form-label">Выберите опцию</label>
                                    <select class="input-adaptive">
                                        <option>Опция 1</option>
                                        <option>Опция 2</option>
                                        <option>Опция 3</option>
                                    </select>
                                </div>
                                <div class="adaptive-form-group md:col-span-2">
                                    <label class="adaptive-form-label">Сообщение</label>
                                    <textarea class="input-adaptive" rows="4" placeholder="Введите ваше сообщение"></textarea>
                                </div>
                            </div>
                            <div class="mt-adaptive-lg">
                                <button type="submit" class="btn-adaptive bg-adaptive-primary text-white hover:bg-adaptive-primary-hover">
                                    Отправить форму
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Сетки -->
                <section id="grid" class="mb-adaptive-2xl">
                    <div class="card-adaptive">
                        <h2 class="adaptive-heading-2 mb-adaptive-lg">Адаптивные сетки</h2>
                        
                        <div class="space-y-adaptive-lg">
                            <div>
                                <h3 class="adaptive-heading-3 mb-adaptive-base">Основная сетка</h3>
                                <p class="adaptive-text-body mb-adaptive-base">
                                    На мобильных: 1 колонка, на планшетах: 2 колонки, на десктопах: 4 колонки
                                </p>
                                <div class="grid-adaptive gap-adaptive-base">
                                    <div class="card-adaptive bg-blue-50 text-center">
                                        <div class="text-adaptive-lg font-bold text-blue-600">1</div>
                                        <p class="adaptive-text-small">Элемент сетки</p>
                                    </div>
                                    <div class="card-adaptive bg-green-50 text-center">
                                        <div class="text-adaptive-lg font-bold text-green-600">2</div>
                                        <p class="adaptive-text-small">Элемент сетки</p>
                                    </div>
                                    <div class="card-adaptive bg-purple-50 text-center">
                                        <div class="text-adaptive-lg font-bold text-purple-600">3</div>
                                        <p class="adaptive-text-small">Элемент сетки</p>
                                    </div>
                                    <div class="card-adaptive bg-orange-50 text-center">
                                        <div class="text-adaptive-lg font-bold text-orange-600">4</div>
                                        <p class="adaptive-text-small">Элемент сетки</p>
                                    </div>
                                    <div class="card-adaptive bg-red-50 text-center">
                                        <div class="text-adaptive-lg font-bold text-red-600">5</div>
                                        <p class="adaptive-text-small">Элемент сетки</p>
                                    </div>
                                    <div class="card-adaptive bg-teal-50 text-center">
                                        <div class="text-adaptive-lg font-bold text-teal-600">6</div>
                                        <p class="adaptive-text-small">Элемент сетки</p>
                                    </div>
                                    <div class="card-adaptive bg-indigo-50 text-center">
                                        <div class="text-adaptive-lg font-bold text-indigo-600">7</div>
                                        <p class="adaptive-text-small">Элемент сетки</p>
                                    </div>
                                    <div class="card-adaptive bg-pink-50 text-center">
                                        <div class="text-adaptive-lg font-bold text-pink-600">8</div>
                                        <p class="adaptive-text-small">Элемент сетки</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Таблицы -->
                <section class="mb-adaptive-2xl">
                    <div class="adaptive-table-container">
                        <h2 class="adaptive-heading-2 mb-adaptive-lg p-adaptive-lg">Адаптивные таблицы</h2>
                        <table class="adaptive-table">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Размер</th>
                                    <th>Описание</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Мобильная версия</td>
                                    <td>320px - 768px</td>
                                    <td>Оптимизирована для телефонов</td>
                                    <td class="text-adaptive-success">Активна</td>
                                </tr>
                                <tr>
                                    <td>Планшетная версия</td>
                                    <td>768px - 1024px</td>
                                    <td>Оптимизирована для планшетов</td>
                                    <td class="text-adaptive-success">Активна</td>
                                </tr>
                                <tr>
                                    <td>Десктопная версия</td>
                                    <td>1024px+</td>
                                    <td>Оптимизирована для больших экранов</td>
                                    <td class="text-adaptive-success">Активна</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Отзывчивость -->
                <section class="mb-adaptive-2xl">
                    <div class="card-adaptive">
                        <h2 class="adaptive-heading-2 mb-adaptive-lg">Тестирование отзывчивости</h2>
                        
                        <div class="space-y-adaptive-base">
                            <div class="p-adaptive-base bg-blue-50 rounded-adaptive-base">
                                <h3 class="adaptive-heading-4 hide-mobile">Этот заголовок скрыт на мобильных устройствах</h3>
                                <h3 class="adaptive-heading-4 show-mobile">Этот заголовок показан только на мобильных устройствах</h3>
                            </div>
                            
                            <div class="p-adaptive-base bg-green-50 rounded-adaptive-base">
                                <h3 class="adaptive-heading-4 hide-tablet">Этот заголовок скрыт на планшетах</h3>
                                <h3 class="adaptive-heading-4 show-tablet">Этот заголовок показан только на планшетах</h3>
                            </div>
                            
                            <div class="p-adaptive-base bg-purple-50 rounded-adaptive-base">
                                <h3 class="adaptive-heading-4 hide-desktop">Этот заголовок скрыт на десктопах</h3>
                                <h3 class="adaptive-heading-4 show-desktop">Этот заголовок показан только на десктопах</h3>
                            </div>
                        </div>
                        
                        <div class="mt-adaptive-lg p-adaptive-base bg-adaptive-background rounded-adaptive-base">
                            <h3 class="adaptive-heading-4 mb-adaptive-sm">Инструкции для тестирования:</h3>
                            <ul class="adaptive-text-body space-y-adaptive-xs">
                                <li>1. Измените размер окна браузера</li>
                                <li>2. Используйте инструменты разработчика для эмуляции устройств</li>
                                <li>3. Проверьте на реальных устройствах</li>
                                <li>4. Обратите внимание на сохранение визуального стиля</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Календарь -->
                <section class="mb-adaptive-2xl">
                    <div class="card-adaptive">
                        <h2 class="adaptive-heading-2 mb-adaptive-lg">Адаптивный календарь</h2>
                        
                        <div class="adaptive-calendar" style="--masters-count: 3">
                            <div class="adaptive-calendar-grid">
                                <!-- Заголовок времени -->
                                <div class="adaptive-calendar-time bg-adaptive-primary text-white font-bold">
                                    Время
                                </div>
                                <div class="adaptive-calendar-time bg-adaptive-primary text-white font-bold">
                                    Мастер 1
                                </div>
                                <div class="adaptive-calendar-time bg-adaptive-primary text-white font-bold">
                                    Мастер 2
                                </div>
                                <div class="adaptive-calendar-time bg-adaptive-primary text-white font-bold">
                                    Мастер 3
                                </div>
                                
                                <!-- Временные слоты -->
                                <div class="adaptive-calendar-time">09:00</div>
                                <div class="adaptive-calendar-cell">
                                    <div class="adaptive-calendar-appointment bg-blue-500">
                                        Клиент А
                                    </div>
                                </div>
                                <div class="adaptive-calendar-cell"></div>
                                <div class="adaptive-calendar-cell">
                                    <div class="adaptive-calendar-appointment bg-green-500">
                                        Клиент Б
                                    </div>
                                </div>
                                
                                <div class="adaptive-calendar-time">10:00</div>
                                <div class="adaptive-calendar-cell"></div>
                                <div class="adaptive-calendar-cell">
                                    <div class="adaptive-calendar-appointment bg-purple-500">
                                        Клиент В
                                    </div>
                                </div>
                                <div class="adaptive-calendar-cell"></div>
                                
                                <div class="adaptive-calendar-time">11:00</div>
                                <div class="adaptive-calendar-cell">
                                    <div class="adaptive-calendar-appointment bg-orange-500">
                                        Клиент Г
                                    </div>
                                </div>
                                <div class="adaptive-calendar-cell"></div>
                                <div class="adaptive-calendar-cell">
                                    <div class="adaptive-calendar-appointment bg-red-500">
                                        Клиент Д
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Информационный блок -->
                <section class="text-center">
                    <div class="card-adaptive bg-gradient-to-r from-blue-50 to-indigo-50">
                        <h2 class="adaptive-heading-2 text-adaptive-primary mb-adaptive-base">
                            Готово к использованию!
                        </h2>
                        <p class="adaptive-text-body text-adaptive-text-secondary mb-adaptive-lg">
                            Эта адаптивная система дизайна готова к использованию в вашем проекте. 
                            Все элементы сохраняют визуальную идентичность на всех устройствах.
                        </p>
                        <div class="flex-adaptive justify-center">
                            <button class="btn-adaptive bg-adaptive-primary text-white hover:bg-adaptive-primary-hover">
                                Использовать в проекте
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Подвал -->
        <footer class="bg-adaptive-surface border-t border-adaptive-border">
            <div class="container-adaptive py-adaptive-lg">
                <div class="text-center">
                    <p class="adaptive-text-small text-adaptive-text-secondary">
                        © 2024 Адаптивная система дизайна. Все права защищены.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html> 