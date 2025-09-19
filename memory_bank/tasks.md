# Задачи проекта

## Текущие приоритетные задачи

### 1. Исправление проблемы изоляции данных между пользователями
- [x] Исправить контроллер CalendarController для фильтрации мастеров по salon_id
- [x] Проверить и исправить фильтрацию данных в SalonMastersController
- [x] Проверить и исправить фильтрацию данных в SalonScheduleController
- [x] Добавить проверки авторизации во все методы контроллеров
- [x] Тестирование изоляции данных между пользователями

### 2. Улучшение UI дизайна для страниц салона
- [x] Разработать современный UI для страницы /salon/masters
- [x] Разработать современный UI для страницы /salon/schedules
- [x] Использовать компоненты Blade и TailwindCSS для создания согласованного дизайна
- [x] Добавить адаптивность для мобильных устройств

### 3. Исправление отображения записей в календаре
- [x] Модифицировать отображение записей в календаре для корректного отображения длительности
- [x] Реализовать заполнение ячеек календаря в соответствии с фактической длительностью записи
- [x] Исправить логику отображения записей, занимающих несколько временных слотов
- [x] Добавить визуальное отображение статуса записи

## План реализации

### 1. Исправление проблемы изоляции данных между пользователями

#### 1.1 Исправление CalendarController
```php
// В методе day() заменить
$masters = Master::where('is_active', true)->get();

// На
$masters = Master::when($user->isSalon(), function($query) use ($user) {
    return $query->where('salon_id', $user->id);
})->where('is_active', true)->get();
```

#### 1.2 Проверка SalonMastersController
- Проверить метод index() на корректную фильтрацию мастеров по salon_id
- Проверить метод addMaster() на корректную проверку авторизации
- Проверить метод removeMaster() на корректную проверку авторизации
- Проверить метод store() на корректную привязку мастера к салону

#### 1.3 Проверка SalonScheduleController
- Проверить метод index() на корректную фильтрацию мастеров по salon_id
- Проверить метод create() на корректную проверку принадлежности мастера к салону
- Проверить метод store() на корректную проверку принадлежности мастера к салону
- Проверить метод edit() на корректную проверку принадлежности мастера к салону
- Проверить метод update() на корректную проверку принадлежности мастера к салону
- Проверить метод destroy() на корректную проверку принадлежности мастера к салону

#### 1.4 Тестирование изоляции данных
- Создать двух пользователей с типом "салон"
- Создать мастеров для каждого салона
- Проверить, что каждый салон видит только своих мастеров
- Проверить, что каждый салон может управлять только своими мастерами
- Проверить, что каждый салон может управлять только расписанием своих мастеров

### 2. Улучшение UI дизайна для страниц салона

#### 2.1 Разработка UI для страницы /salon/masters
- Создать компонент для отображения карточки мастера
- Создать компонент для формы добавления мастера
- Использовать TailwindCSS для стилизации компонентов
- Добавить анимации и интерактивные элементы

```html
<!-- Пример компонента карточки мастера -->
<div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow">
  <div class="flex items-center">
    <img src="{{ $master->photo_url }}" alt="{{ $master->name }}" class="w-16 h-16 rounded-full mr-4">
    <div>
      <h3 class="text-lg font-semibold">{{ $master->name }}</h3>
      <p class="text-gray-600">{{ $master->specialization }}</p>
    </div>
  </div>
  <div class="mt-4 flex justify-end">
    <button class="text-blue-500 hover:text-blue-700 mr-2">Редактировать</button>
    <button class="text-red-500 hover:text-red-700">Удалить</button>
  </div>
</div>
```

#### 2.2 Разработка UI для страницы /salon/schedules
- Создать компонент для отображения календаря
- Создать компонент для отображения расписания мастера
- Создать компонент для формы редактирования расписания
- Использовать TailwindCSS для стилизации компонентов

```html
<!-- Пример компонента календаря -->
<div class="bg-white rounded-lg shadow-md p-4">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-semibold">{{ $month }} {{ $year }}</h2>
    <div>
      <button class="px-3 py-1 bg-gray-200 rounded-md hover:bg-gray-300">&lt;</button>
      <button class="px-3 py-1 bg-gray-200 rounded-md hover:bg-gray-300">&gt;</button>
    </div>
  </div>
  <div class="grid grid-cols-7 gap-1">
    <!-- Дни недели -->
    <div class="text-center font-medium text-gray-500">Пн</div>
    <div class="text-center font-medium text-gray-500">Вт</div>
    <div class="text-center font-medium text-gray-500">Ср</div>
    <div class="text-center font-medium text-gray-500">Чт</div>
    <div class="text-center font-medium text-gray-500">Пт</div>
    <div class="text-center font-medium text-gray-500">Сб</div>
    <div class="text-center font-medium text-gray-500">Вс</div>
    
    <!-- Дни месяца -->
    @foreach($calendar as $week)
      @foreach($week as $day)
        <div class="aspect-square p-1 border rounded-md {{ $day['is_current_month'] ? 'bg-white' : 'bg-gray-100' }} {{ $day['is_today'] ? 'border-blue-500' : 'border-gray-200' }}">
          <div class="text-sm">{{ $day['date']->format('j') }}</div>
          @if($day['has_schedule'])
            <div class="mt-1 text-xs {{ $day['schedule']->is_day_off ? 'text-red-500' : 'text-green-500' }}">
              {{ $day['schedule']->is_day_off ? 'Выходной' : $day['schedule']->working_hours }}
            </div>
          @endif
        </div>
      @endforeach
    @endforeach
  </div>
</div>
```

### 3. Исправление отображения записей в календаре

#### 3.1 Модификация отображения записей
- Изменить верстку календаря для поддержки записей разной длительности
- Добавить расчет высоты ячейки записи в зависимости от длительности

```html
<!-- Пример модифицированного отображения записи -->
<div class="appointment" 
     style="grid-row: {{ $startRow }} / {{ $endRow }}; grid-column: {{ $column }};"
     data-appointment-id="{{ $appointment->id }}">
  <div class="appointment-content bg-blue-100 border border-blue-300 p-2 rounded h-full overflow-hidden">
    <div class="text-sm font-medium">{{ $appointment->client->name }}</div>
    <div class="text-xs">{{ $appointment->service->name }}</div>
    <div class="text-xs text-gray-600">
      {{ Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - 
      {{ Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}
    </div>
  </div>
</div>
```

#### 3.2 Реализация заполнения ячеек календаря
- Изменить метод buildCalendarData() в CalendarController для расчета позиции и размера записи
- Добавить CSS для корректного отображения записей разной длительности

```php
private function buildCalendarData($masters, $appointments, $timeSlots, $schedules): array
{
    $data = [];
    
    foreach ($masters as $master) {
        $schedule = $schedules->get($master->user_id ?? $master->id);
        
        // Check if master is working today
        $isWorking = $schedule && !$schedule->is_day_off;
        
        $masterAppointments = $appointments->where('master_id', $master->id)->values();
        
        // Calculate position and size for each appointment
        $formattedAppointments = [];
        foreach ($masterAppointments as $appointment) {
            $startTime = Carbon::parse($appointment->start_time);
            $endTime = Carbon::parse($appointment->end_time);
            
            // Calculate row positions (each row is 30 minutes)
            $startRow = $this->calculateRowPosition($startTime, $timeSlots);
            $endRow = $this->calculateRowPosition($endTime, $timeSlots);
            
            // Ensure minimum height of 1 row
            if ($startRow === $endRow) {
                $endRow = $startRow + 1;
            }
            
            $formattedAppointments[] = [
                'appointment' => $appointment,
                'startRow' => $startRow,
                'endRow' => $endRow,
            ];
        }
        
        $data[$master->id] = [
            'master' => $master,
            'schedule' => $schedule,
            'is_working' => $isWorking,
            'appointments' => $formattedAppointments
        ];
    }
    
    return $data;
}

private function calculateRowPosition($time, $timeSlots)
{
    $timeString = $time->format('H:i');
    $position = array_search($timeString, $timeSlots);
    
    if ($position === false) {
        // If exact time not found, calculate based on closest slot
        $hour = (int)$time->format('H');
        $minute = (int)$time->format('i');
        $position = ($hour - 9) * 2; // 9:00 is the first slot
        
        if ($minute >= 30) {
            $position += 1;
        }
    }
    
    return $position + 1; // +1 because CSS grid is 1-indexed
}
```

#### 3.3 Добавление CSS для календаря
```css
.calendar-grid {
  display: grid;
  grid-template-columns: [times] auto repeat(var(--num-masters), 1fr);
  grid-template-rows: [header] auto repeat(var(--num-slots), 30px);
  gap: 1px;
}

.time-slot {
  grid-column: times;
  border-right: 1px solid #e2e8f0;
  padding-right: 8px;
  text-align: right;
  font-size: 0.75rem;
  color: #64748b;
}

.master-column {
  grid-row: header;
  padding: 8px;
  text-align: center;
  font-weight: 600;
  background-color: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.appointment {
  position: relative;
  margin: 1px;
  overflow: hidden;
}

.appointment-content {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
}
```

## Остальные приоритетные задачи

### Исправление ошибок мультиарендности
- [ ] Аудит всех контроллеров на корректную фильтрацию по user_id
- [ ] Исправление метода миграции клиентов и услуг к пользователям

### Улучшение валидации форм
- [ ] Исправление валидации при создании записей
- [ ] Добавление валидации уникальности с учетом user_id
- [ ] Добавление клиентской валидации форм
- [ ] Улучшение сообщений об ошибках

### Исправление проблем с календарем
- [ ] Улучшение алгоритма определения доступных слотов времени
- [ ] Исправление проблем с пересечением записей
- [ ] Оптимизация загрузки календаря

## Задачи по улучшению функциональности

### Улучшение пользовательского интерфейса
- [ ] Адаптация интерфейса для мобильных устройств
- [ ] Добавление интерактивных элементов в календарь
- [ ] Улучшение навигации по приложению
- [ ] Оптимизация форм для быстрого заполнения

### Добавление уведомлений
- [ ] Реализация системы уведомлений
- [ ] Добавление уведомлений о новых записях
- [ ] Добавление напоминаний о предстоящих записях
- [ ] Настройка уведомлений для пользователей

### Добавление аналитики
- [ ] Реализация статистики по записям
- [ ] Добавление отчетов по выручке
- [ ] Анализ загруженности мастеров
- [ ] Экспорт отчетов в различных форматах

## Задачи по оптимизации

### Оптимизация базы данных
- [ ] Анализ и оптимизация запросов
- [ ] Добавление индексов для ускорения поиска
- [ ] Реализация кэширования часто используемых данных
- [ ] Оптимизация работы с отношениями в моделях

### Улучшение производительности
- [ ] Оптимизация загрузки страниц
- [ ] Минимизация и сжатие CSS и JavaScript
- [ ] Оптимизация изображений
- [ ] Внедрение ленивой загрузки для тяжелых компонентов

### Улучшение архитектуры
- [ ] Выделение бизнес-логики в сервисный слой
- [ ] Рефакторинг дублирующегося кода в контроллерах
- [ ] Добавление документации к коду
- [ ] Увеличение покрытия тестами

## Задачи на будущее

### Расширение функциональности
- [ ] Интеграция с платежными системами
- [ ] Добавление системы лояльности для клиентов
- [ ] Реализация API для мобильных приложений
- [ ] Интеграция с календарями (Google, Apple)

### Улучшение безопасности
- [ ] Проведение аудита безопасности
- [ ] Внедрение двухфакторной аутентификации
- [ ] Улучшение защиты от CSRF, XSS и SQL-инъекций
- [ ] Шифрование чувствительных данных

### Инфраструктура
- [ ] Настройка CI/CD
- [ ] Внедрение системы мониторинга
- [ ] Настройка автоматического бэкапа данных
- [ ] Оптимизация развертывания 

## Task: Adapt /schedules page for mobile devices

**Description:** Adapt the page at http://127.0.0.1:8000/schedules for phones, without changing details and design, leaving the same grid.

**Complexity Level:** Level 2 - Simple Enhancement

**Requirements:**
- Preserve all existing details and visual design on desktop
- Maintain the same grid structure but make it responsive
- Ensure proper display on phone screen sizes (e.g., width < 768px)
- Use Tailwind responsive classes

**Subtasks:**
- [x] Analyze current structure of resources/views/schedules/index.blade.php
- [x] Identify grid and layout elements
- [x] Add responsive Tailwind classes to make it mobile-friendly while preserving the grid
- [x] Verify no changes to details or overall design
- [x] Test on mobile viewport

**Time Estimates:**
- Analysis: 10 minutes
- Identification: 5 minutes
- Adding classes: 15 minutes
- Verification: 10 minutes
- Testing: 10 minutes
- Total: ~50 minutes

**Dependencies:**
- Tailwind CSS configured in the project
- Access to the view file
- Running local server for testing

**Status:** Completed

**Archive:** ../archive/archive-schedules-mobile.md 