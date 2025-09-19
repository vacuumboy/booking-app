<?php

namespace App\Http\Controllers;

use App\Models\Master;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SalonScheduleController extends Controller
{
    /**
     * Показать страницу управления расписанием мастеров салона
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут управлять расписанием мастеров');
        }
        
        // Получаем мастеров салона через связь многие-ко-многим
        $masters = $user->masters()
            ->where('masters.is_active', true)
            ->orderBy('masters.name')
            ->get();
            
        // Если нет мастеров, показываем страницу с сообщением
        if ($masters->isEmpty()) {
            return view('salon.schedules.no-masters');
        }
        
        // Для отладки - логируем всех мастеров
        \Log::debug('Мастера салона: ' . json_encode($masters->map(function($master) {
            return [
                'id' => $master->id,
                'name' => $master->name,
                'email' => $master->email,
                'user_id' => $master->user_id
            ];
        })));
        
        // Получаем выбранного мастера или первого из списка
        $selectedMasterId = $request->get('master_id', $masters->first()->id);
        $selectedMaster = $masters->find($selectedMasterId);
        
        // Если выбранный мастер не найден, используем первого мастера
        if (!$selectedMaster) {
            $selectedMaster = $masters->first();
            $selectedMasterId = $selectedMaster->id;
            \Log::debug('Выбранный мастер не найден, используем первого: ' . $selectedMasterId);
        }
        
        // Получаем текущий месяц или указанный месяц
        $currentDate = Carbon::now();
        $month = $request->get('month', $currentDate->month);
        $year = $request->get('year', $currentDate->year);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Вычисляем фактический диапазон календаря (включая дни из предыдущего/следующего месяцев)
        $calendarStart = $startDate->copy()->startOfWeek();
        $calendarEnd = $endDate->copy()->endOfWeek();
        
        try {
            // Получаем связанного пользователя (мастера)
            $result = $this->validateMasterAndGetUser($selectedMasterId, $user->id);
            
            if (!is_array($result)) {
                // Это означает, что метод вернул redirect с ошибкой
                \Log::warning('Ошибка валидации мастера: ' . $selectedMasterId);
                
                // Если с выбранным мастером проблема, пробуем найти другого мастера
                $validMaster = false;
                
                foreach ($masters as $master) {
                    if ($master->id !== $selectedMasterId) {
                        \Log::debug('Пробуем альтернативного мастера: ' . $master->id);
                        $resultAlt = $this->validateMasterAndGetUser($master->id, $user->id);
                        if (is_array($resultAlt)) {
                            // Нашли подходящего мастера
                            $selectedMaster = $master;
                            $result = $resultAlt;
                            $validMaster = true;
                            
                            \Log::debug('Найден подходящий мастер: ' . $master->id);
                            
                            // Не добавляем предупреждение о смене мастера
                            break;
                        }
                    }
                }
                
                // Если не нашли ни одного подходящего мастера
                if (!$validMaster) {
                    \Log::error('Не найдено ни одного подходящего мастера для салона: ' . $user->id);
                    
                    // Создаем временный объект для отображения календаря без заполнения расписаний
                    session()->flash('error', 'Ни один из ваших мастеров не имеет активного аккаунта пользователя. Пожалуйста, убедитесь, что мастера зарегистрированы в системе.');
                    
                    // Используем ID самого салона как запасной вариант
                    $masterUser = $user;
                    $schedules = collect();
                } else {
                    $masterUser = $result['user'];
                    
                    // Получаем расписание для всего календарного представления
                    $schedules = Schedule::where('master_id', $selectedMaster->id)
                        ->dateRange($calendarStart, $calendarEnd)
                        ->orderBy('date')
                        ->get()
                        ->keyBy(function ($schedule) {
                            return $schedule->date->format('Y-m-d');
                        });
                }
            } else {
                $masterUser = $result['user'];
                
                // Получаем расписание для всего календарного представления
                $schedules = Schedule::where('master_id', $selectedMaster->id)
                    ->dateRange($calendarStart, $calendarEnd)
                    ->orderBy('date')
                    ->get()
                    ->keyBy(function ($schedule) {
                        return $schedule->date->format('Y-m-d');
                    });
            }
            
            // Генерируем данные календаря
            $calendar = $this->generateCalendarData($startDate, $endDate, $schedules);
            
            return view('salon.schedules.index', compact(
                'masters',
                'selectedMaster',
                'calendar',
                'schedules',
                'startDate',
                'endDate'
            ));
        } catch (\Exception $e) {
            \Log::error('Ошибка при загрузке страницы расписаний: ' . $e->getMessage(), [
                'exception' => $e,
                'selected_master_id' => $selectedMasterId
            ]);
            
            return back()->withErrors(['error' => 'Произошла ошибка при загрузке расписаний: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Показать форму для создания расписания мастера
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут управлять расписанием мастеров');
        }
        
        $masterId = $request->get('master_id');
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        // Проверяем мастера и получаем связанного пользователя
        $result = $this->validateMasterAndGetUser($masterId, $user->id);
        if (!is_array($result)) {
            return $result; // Возвращаем ошибку
        }
        
        $master = $result['master'];
        $masterUser = $result['user'];
        
        // Проверяем, существует ли уже расписание на эту дату
        $existingSchedule = Schedule::where('master_id', $master->id)
            ->whereDate('date', $date)
            ->first();
            
        if ($existingSchedule) {
            return redirect()->route('salon.schedules.edit', [
                'schedule' => $existingSchedule->id,
                'master_id' => $master->id
            ]);
        }
        
        return view('salon.schedules.create', compact('master', 'date'));
    }
    
    /**
     * Сохранить новое расписание мастера
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут управлять расписанием мастеров');
        }
        
        $validated = $request->validate([
            'master_id' => 'required|exists:masters,id',
            'date' => 'required|date|after_or_equal:today',
            'is_day_off' => 'boolean',
            'start_time' => 'required_if:is_day_off,0|nullable|date_format:H:i',
            'end_time' => 'required_if:is_day_off,0|nullable|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'recurring_until' => 'nullable|date|after:date',
        ], [
            'date.required' => 'Дата обязательна для заполнения.',
            'date.after_or_equal' => 'Дата не может быть в прошлом.',
            'start_time.required_if' => 'Время начала обязательно для рабочего дня.',
            'end_time.required_if' => 'Время окончания обязательно для рабочего дня.',
            'end_time.after' => 'Время окончания должно быть позже времени начала.',
            'notes.max' => 'Заметки не должны превышать 500 символов.',
            'recurring_until.after' => 'Дата окончания повторения должна быть позже начальной даты.',
        ]);
        
        // Проверяем мастера и получаем связанного пользователя
        $result = $this->validateMasterAndGetUser($validated['master_id'], $user->id);
        if (!is_array($result)) {
            return $result; // Возвращаем ошибку
        }
        
        $master = $result['master'];
        $masterUser = $result['user'];
        
        // Проверяем, существует ли уже расписание на эту дату
        $existingSchedule = Schedule::where('master_id', $master->id)
            ->whereDate('date', $validated['date'])
            ->first();
            
        if ($existingSchedule) {
            return back()->withErrors(['date' => 'Расписание на эту дату уже существует.']);
        }
        
        // Создаем основное расписание
        $isDayOff = isset($validated['is_day_off']) ? (bool)$validated['is_day_off'] : false;
        
        $schedule = Schedule::create([
            'master_id' => $master->id,
            'date' => $validated['date'],
            'is_day_off' => $isDayOff,
            'start_time' => $isDayOff ? null : ($validated['start_time'] ?? null),
            'end_time' => $isDayOff ? null : ($validated['end_time'] ?? null),
            'notes' => $validated['notes'] ?? null,
            'is_recurring' => isset($validated['is_recurring']) ? (bool)$validated['is_recurring'] : false,
            'recurring_until' => $validated['recurring_until'] ?? null,
        ]);
        
        // Создаем повторяющиеся расписания, если необходимо
        if (isset($validated['is_recurring']) && $validated['is_recurring'] && $validated['recurring_until']) {
            $this->createRecurringSchedules($master->id, $validated);
        }
        
        return redirect()->route('salon.schedules.index', ['master_id' => $master->id])
            ->with('success', 'Расписание успешно создано!');
    }
    
    /**
     * Показать форму редактирования расписания мастера
     */
    public function edit(Request $request, Schedule $schedule): View|RedirectResponse
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут управлять расписанием мастеров');
        }
        
        $masterId = $request->get('master_id');
        
        // Проверяем мастера и получаем связанного пользователя
        $result = $this->validateMasterAndGetUser($masterId, $user->id);
        if (!is_array($result)) {
            return $result; // Возвращаем ошибку
        }
        
        $master = $result['master'];
        $masterUser = $result['user'];
        
        if ($schedule->master_id !== $master->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }
        
        return view('salon.schedules.edit', compact('schedule', 'master'));
    }
    
    /**
     * Обновить расписание мастера
     */
    public function update(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут управлять расписанием мастеров');
        }
        
        $masterId = $request->get('master_id');
        
        // Проверяем мастера и получаем связанного пользователя
        $result = $this->validateMasterAndGetUser($masterId, $user->id);
        if (!is_array($result)) {
            return $result; // Возвращаем ошибку
        }
        
        $master = $result['master'];
        $masterUser = $result['user'];
        
        if ($schedule->master_id !== $master->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }
        
        $validated = $request->validate([
            'is_day_off' => 'boolean',
            'start_time' => 'required_if:is_day_off,0|nullable|date_format:H:i',
            'end_time' => 'required_if:is_day_off,0|nullable|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:500',
        ], [
            'start_time.required_if' => 'Время начала обязательно для рабочего дня.',
            'end_time.required_if' => 'Время окончания обязательно для рабочего дня.',
            'end_time.after' => 'Время окончания должно быть позже времени начала.',
            'notes.max' => 'Заметки не должны превышать 500 символов.',
        ]);
        
        $isDayOff = isset($validated['is_day_off']) ? (bool)$validated['is_day_off'] : false;
        
        $schedule->update([
            'is_day_off' => $isDayOff,
            'start_time' => $isDayOff ? null : ($validated['start_time'] ?? null),
            'end_time' => $isDayOff ? null : ($validated['end_time'] ?? null),
            'notes' => $validated['notes'] ?? null,
        ]);
        
        return redirect()->route('salon.schedules.index', ['master_id' => $master->id])
            ->with('success', 'Расписание успешно обновлено!');
    }
    
    /**
     * Удалить расписание мастера
     */
    public function destroy(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут управлять расписанием мастеров');
        }
        
        $masterId = $request->get('master_id');
        
        // Проверяем мастера и получаем связанного пользователя
        $result = $this->validateMasterAndGetUser($masterId, $user->id);
        if (!is_array($result)) {
            return $result; // Возвращаем ошибку
        }
        
        $master = $result['master'];
        $masterUser = $result['user'];
        
        if ($schedule->master_id !== $master->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }
        
        $schedule->delete();
        
        return redirect()->route('salon.schedules.index', ['master_id' => $master->id])
            ->with('success', 'Расписание успешно удалено!');
    }
    
    /**
     * Создать повторяющиеся расписания
     */
    private function createRecurringSchedules($masterId, $data)
    {
        $startDate = Carbon::parse($data['date'])->addDay();
        $endDate = Carbon::parse($data['recurring_until']);
        $dayOfWeek = Carbon::parse($data['date'])->dayOfWeek;
        $isDayOff = isset($data['is_day_off']) ? (bool)$data['is_day_off'] : false;

        $current = $startDate->copy();
        while ($current <= $endDate) {
            if ($current->dayOfWeek === $dayOfWeek) {
                // Проверяем, не существует ли уже расписание
                $exists = Schedule::where('master_id', $masterId)
                    ->whereDate('date', $current->format('Y-m-d'))
                    ->exists();

                if (!$exists) {
                    Schedule::create([
                        'master_id' => $masterId,
                        'date' => $current->format('Y-m-d'),
                        'is_day_off' => $isDayOff,
                        'start_time' => $isDayOff ? null : ($data['start_time'] ?? null),
                        'end_time' => $isDayOff ? null : ($data['end_time'] ?? null),
                        'notes' => $data['notes'] ?? null,
                    ]);
                }
            }
            $current->addDay();
        }
    }
    
    /**
     * Генерировать данные календаря для представления
     */
    private function generateCalendarData($startDate, $endDate, $schedules)
    {
        $calendar = [];
        $current = $startDate->copy()->startOfWeek();
        $monthEnd = $endDate->copy()->endOfWeek();

        while ($current <= $monthEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $current->format('Y-m-d');
                $schedule = $schedules->get($dateKey);
                
                $week[] = [
                    'date' => $current->copy(),
                    'is_current_month' => $current->month === $startDate->month,
                    'is_today' => $current->isToday(),
                    'schedule' => $schedule,
                    'has_schedule' => !is_null($schedule),
                ];
                
                $current->addDay();
            }
            $calendar[] = $week;
        }

        return $calendar;
    }

    /**
     * Проверяет принадлежность мастера салону и возвращает связанного пользователя
     * @return array|RedirectResponse Массив ['master' => $master, 'user' => $masterUser] или ответ с ошибкой
     */
    private function validateMasterAndGetUser($masterId, $salonId)
    {
        // Получаем пользователя-салон
        $salon = User::find($salonId);
        if (!$salon || !$salon->isSalon()) {
            return back()->withErrors(['salon' => 'Салон не найден']);
        }
        
        // Проверяем, принадлежит ли мастер этому салону через связь многие-ко-многим
        $master = $salon->masters()->where('masters.id', $masterId)->first();
        
        if (!$master) {
            return back()->withErrors(['master' => 'Мастер не найден или не принадлежит вашему салону']);
        }
        
        // 1. Сначала пробуем найти по прямой связи user_id
        if ($master->user_id) {
            $masterUser = User::where('id', $master->user_id)
                ->where('user_type', 'master')
                ->first(); // Убрали проверку is_active
                
            if ($masterUser) {
                return ['master' => $master, 'user' => $masterUser];
            }
        }
        
        // 2. Если нет прямой связи, пробуем найти по email или телефону
        $masterUser = User::where(function($query) use ($master) {
                $query->where('email', $master->email)
                      ->orWhere('phone', $master->phone);
            })
            ->where('user_type', 'master')
            ->first(); // Убрали проверку is_active
        
        if (!$masterUser) {
            // 3. Если не нашли подходящего пользователя, используем владельца салона
            $masterUser = User::where('id', $salonId)
                ->where('user_type', 'salon')
                ->where('is_active', true)
                ->first();
                
            if ($masterUser) {
                // Используем владельца салона без вывода предупреждений
                return ['master' => $master, 'user' => $masterUser];
            }
            
            return back()->withErrors([
                'master' => 'Не найден аккаунт пользователя для мастера "' . $master->name . '". ' .
                           'Мастер должен создать аккаунт с тем же email или телефоном.'
            ]);
        }
        
        // Сохраняем найденную связь для будущего использования
        if ($masterUser->id !== $master->user_id) {
            $master->user_id = $masterUser->id;
            $master->save();
        }
        
        return ['master' => $master, 'user' => $masterUser];
    }
} 