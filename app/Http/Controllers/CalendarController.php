<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Master;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Show main calendar page - redirects to today's calendar
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('calendar.day', ['date' => Carbon::today()->format('Y-m-d')]);
    }

    /**
     * Show daily calendar view
     */
    public function day(Request $request, $date = null): View
    {
        $user = $request->user();
        // Если дата не передана как параметр маршрута, используем сегодняшнюю дату
        if (!$date) {
            $date = Carbon::today()->format('Y-m-d');
        }
        
        // Убеждаемся, что дата в правильном формате
        $selectedDate = Carbon::parse($date)->startOfDay();
        

        
        // Get masters based on user type
        if ($user->isMaster()) {
            // For master user - show only their appointments
            // Сначала ищем мастера, связанного с пользователем
            $masters = Master::where('user_id', $user->id)->get();
            
            // Если не найден по user_id, ищем по email (для обратной совместимости)
            if ($masters->count() == 0) {
                $masters = Master::where('email', $user->email)->get();
            }
            
            // Если мастер не найден, создаем профиль мастера
            if ($masters->count() == 0) {
                $master = Master::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'user_id' => $user->id,
                    'is_active' => true,
                ]);
                $masters = collect([$master]);
            }
        } else if ($user->isSalon()) {
            // For salon user - show only masters associated with this salon
            $masters = $user->masters()->get();
            
            // Если нет мастеров вообще, показываем страницу с сообщением
            if ($masters->count() == 0) {
                return view('calendar.no-masters', compact('selectedDate'))->with('warning', 'Добавьте хотя бы одного мастера для просмотра календаря.');
            }
        } else {
            // For admin or other user types - show all active masters
            $masters = Master::where('is_active', true)->get();
            
            // Если нет мастеров вообще, показываем сообщение
            if ($masters->count() == 0) {
                return view('calendar.no-masters', compact('selectedDate'))->with('warning', 'В системе нет активных мастеров.');
            }
        }
        
        // Сохраняем изначальное количество мастеров для проверки
        $totalMasters = $masters->count();
        

        
        // Get schedules for masters
        $masterIds = $masters->pluck('id')->filter();
        $userIds = $masters->pluck('user_id')->filter();
        
        $schedules = Schedule::where(function($query) use ($masterIds, $userIds) {
                // Получаем расписания либо по master_id, либо по user_id
                $query->whereIn('master_id', $masterIds)
                      ->orWhereIn('user_id', $userIds);
            })
            ->whereDate('date', $selectedDate)
            ->get();
            
        // Группируем расписания по мастерам для удобного доступа
        $schedulesGrouped = collect();
        foreach ($masters as $master) {
            $masterSchedule = $schedules->where('master_id', $master->id)->first();
            
            // Если не найден по master_id и у мастера есть user_id, ищем по user_id
            if (!$masterSchedule && $master->user_id) {
                $masterSchedule = $schedules->where('user_id', $master->user_id)->first();
            }
            
            if ($masterSchedule) {
                $schedulesGrouped->put($master->id, $masterSchedule);
            }
        }
        
        // Фильтруем мастеров - показываем только тех, кто работает в этот день
        $workingMasters = $masters->filter(function($master) use ($schedulesGrouped) {
            $schedule = $schedulesGrouped->get($master->id);
            // Показываем мастера, если у него есть расписание и он не на выходном
            return $schedule && !$schedule->is_day_off;
        });
        
        // Если нет работающих мастеров, показываем сообщение
        if ($workingMasters->count() == 0) {
            // Если мастера есть в системе, но никто не работает в этот день
            if ($totalMasters > 0) {
                return view('calendar.no-masters', compact('selectedDate'))->with('warning', 'В выбранную дату никто из мастеров не работает.');
            } else {
                // Если мастеров вообще нет в системе (не должно произойти, но на всякий случай)
                return view('calendar.no-masters', compact('selectedDate'))->with('warning', 'В системе нет активных мастеров.');
            }
        }
        
        // Используем только работающих мастеров для дальнейшей логики
        $masters = $workingMasters;
        
        // Обновляем группировку расписаний только для работающих мастеров
        $workingSchedulesGrouped = collect();
        foreach ($masters as $master) {
            $schedule = $schedulesGrouped->get($master->id);
            if ($schedule) {
                $workingSchedulesGrouped->put($master->id, $schedule);
            }
        }
        $schedulesGrouped = $workingSchedulesGrouped;
        
        // Get appointments for the selected date and working masters only (excluding cancelled)
        $masterIds = $masters->pluck('id')->filter();
        $appointments = Appointment::with(['client', 'service', 'master'])
            ->whereDate('start_time', $selectedDate)
            ->where('status', '!=', 'cancelled')
            ->when($masterIds->count() > 0, function($query) use ($masterIds) {
                return $query->whereIn('master_id', $masterIds);
            })
            ->orderBy('start_time')
            ->get();
        
        // Generate time slots based on master schedules and appointments
        $timeSlots = $this->generateTimeSlots($schedulesGrouped, $appointments);
        
        // Group appointments by master and time
        $calendarData = $this->buildCalendarData($masters, $appointments, $timeSlots, $schedulesGrouped);
        
        return view('calendar.day', compact(
            'masters', 
            'appointments', 
            'selectedDate', 
            'timeSlots', 
            'calendarData'
        ));
    }
    
    /**
     * Generate time slots based on master schedules and appointments
     */
    private function generateTimeSlots($schedules, $appointments): array
    {
        $earliestTime = null;
        $latestTime = null;
        $hasWorkingSchedules = false;
        
        // Find the earliest start time and latest end time from working schedules only
        foreach ($schedules as $schedule) {
            if (!$schedule->is_day_off && $schedule->start_time && $schedule->end_time) {
                $hasWorkingSchedules = true;
                $startTime = Carbon::createFromFormat('H:i', $schedule->start_time);
                $endTime = Carbon::createFromFormat('H:i', $schedule->end_time);
                
                if ($earliestTime === null || $startTime->lt($earliestTime)) {
                    $earliestTime = $startTime;
                }
                if ($latestTime === null || $endTime->gt($latestTime)) {
                    $latestTime = $endTime;
                }
            }
        }
        
        // If no working schedules found, check active appointments to set minimum range
        if (!$hasWorkingSchedules && $appointments->count() > 0) {
            foreach ($appointments as $appointment) {
                // Skip cancelled appointments
                if ($appointment->status === 'cancelled') {
                    continue;
                }
                
                $startTime = Carbon::parse($appointment->start_time);
                $endTime = Carbon::parse($appointment->end_time);
                
                if ($earliestTime === null || $startTime->lt($earliestTime)) {
                    $earliestTime = $startTime;
                }
                if ($latestTime === null || $endTime->gt($latestTime)) {
                    $latestTime = $endTime;
                }
            }
        }
        
        // If still no times found, use minimal default range
        if ($earliestTime === null || $latestTime === null) {
            $earliestTime = Carbon::createFromTime(9, 0);
            $latestTime = Carbon::createFromTime(18, 0);
        }
        
        // Round to nearest 30-minute intervals
        $earliestTime = $earliestTime->setMinute(floor($earliestTime->minute / 30) * 30);
        $latestTime = $latestTime->setMinute(ceil($latestTime->minute / 30) * 30);
        
        // Generate slots with 30-minute intervals
        $slots = [];
        $current = $earliestTime->copy();
        
        while ($current <= $latestTime) {
            $slots[] = $current->format('H:i');
            $current->addMinutes(30);
        }
        
        return $slots;
    }
    
    /**
     * Build calendar data structure
     */
    private function buildCalendarData($masters, $appointments, $timeSlots, $schedules): array
    {
        $data = [];
        
        foreach ($masters as $master) {
            $schedule = $schedules->get($master->id);
            
            // Check if master is working today
            $isWorking = $schedule && !$schedule->is_day_off;
            
            $masterAppointments = $appointments->where('master_id', $master->id)->values();
            
            // Calculate occupied time slots for this master (only active appointments)
            $occupiedSlots = [];
            foreach ($masterAppointments as $appointment) {
                // Skip cancelled appointments (though they should already be excluded)
                if ($appointment->status === 'cancelled') {
                    continue;
                }
                
                $startTime = Carbon::parse($appointment->start_time);
                $endTime = Carbon::parse($appointment->end_time);
                
                // Check which time slots overlap with this appointment
                foreach ($timeSlots as $slot) {
                    $slotStart = Carbon::createFromFormat('H:i', $slot);
                    $slotEnd = $slotStart->copy()->addMinutes(30);
                    
                    // If appointment overlaps with this slot, mark it as occupied
                    if ($startTime < $slotEnd && $endTime > $slotStart) {
                        $occupiedSlots[] = $slot;
                    }
                }
            }
            $occupiedSlots = array_unique($occupiedSlots);
            
            // Calculate position and size for each active appointment
            $formattedAppointments = [];
            foreach ($masterAppointments as $appointment) {
                // Skip cancelled appointments (though they should already be excluded)
                if ($appointment->status === 'cancelled') {
                    continue;
                }
                
                $startTime = Carbon::parse($appointment->start_time);
                $endTime = Carbon::parse($appointment->end_time);
                
                // Calculate exact duration in minutes
                $durationMinutes = $startTime->diffInMinutes($endTime);
                
                // Calculate grid row positions
                $startTimeString = $startTime->format('H:i');
                $endTimeString = $endTime->format('H:i');
                
                // Find start and end row positions in grid
                $startPosition = $this->calculateRowPosition($startTime, $timeSlots);
                $endPosition = $this->calculateRowPosition($endTime, $timeSlots);
                
                // Ensure minimum span of 1 row 
                if ($endPosition['row'] <= $startPosition['row']) {
                    $endPosition['row'] = $startPosition['row'] + 1;
                }
                
                $formattedAppointments[] = [
                    'appointment' => $appointment,
                    'startRow' => $startPosition['row'],
                    'endRow' => $endPosition['row'],
                    'startOffset' => $startPosition['offset'],
                    'endOffset' => $endPosition['offset'],
                    'durationMinutes' => $durationMinutes,
                ];
            }
            
            $data[$master->id] = [
                'master' => $master,
                'schedule' => $schedule,
                'is_working' => $isWorking,
                'appointments' => $formattedAppointments,
                'occupied_slots' => $occupiedSlots
            ];
        }
        
        return $data;
    }
    
    /**
     * Calculate row position for a time in the calendar grid
     */
    private function calculateRowPosition($time, $timeSlots)
    {
        $timeInMinutes = $time->hour * 60 + $time->minute;
        
        // Найти первый слот сетки (обычно самое раннее время)
        if (empty($timeSlots)) {
            return ['row' => 2, 'offset' => 0]; // Возвращаем базовую позицию если нет слотов
        }
        
        $firstSlotTime = Carbon::createFromFormat('H:i', $timeSlots[0]);
        $firstSlotMinutes = $firstSlotTime->hour * 60 + $firstSlotTime->minute;
        
        // Вычисляем разность в минутах от первого слота
        $minutesFromStart = $timeInMinutes - $firstSlotMinutes;
        
        // Каждый слот представляет 30 минут, каждая строка = 80px
        $slotIndex = floor($minutesFromStart / 30);
        $offsetMinutes = $minutesFromStart % 30;
        
        // Вычисляем смещение в процентах от высоты ячейки (80px)
        $offsetPercent = ($offsetMinutes / 30) * 100;
        
        $gridRow = $slotIndex + 2; // +2 для заголовка и начала grid-row с 1
        
        return [
            'row' => max(2, $gridRow),
            'offset' => $offsetPercent
        ];
    }
    
    /**
     * Show appointment creation form
     */
    public function createAppointment(Request $request): View
    {
        $user = $request->user();
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $time = $request->get('time', '10:00');
        $masterId = $request->get('master_id');
        
        $selectedDate = Carbon::parse($date);
        
        // Get masters based on user type
        if ($user->isMaster()) {
            // For master user - show only themselves
            // Сначала ищем мастера, связанного с пользователем
            $masters = Master::where('user_id', $user->id)->get();
            
            // Если не найден по user_id, ищем по email (для обратной совместимости)
            if ($masters->count() == 0) {
                $masters = Master::where('email', $user->email)->get();
            }
            
            // Если мастер не найден, создаем профиль мастера
            if ($masters->count() == 0) {
                $master = Master::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'user_id' => $user->id,
                    'is_active' => true,
                ]);
                $masters = collect([$master]);
            }
        } else if ($user->isSalon()) {
            // For salon user - show only masters associated with this salon
            $masters = $user->masters()->get();
            
            // Если нет мастеров, показываем страницу с сообщением
            if ($masters->count() == 0) {
                return view('calendar.no-masters', compact('selectedDate'))->with('warning', 'Добавьте хотя бы одного мастера для создания записи.');
            }
        } else {
            // For admin or other user types - show all active masters
            $masters = Master::where('is_active', true)->get();
            
            // Если нет мастеров, показываем сообщение
            if ($masters->count() == 0) {
                return view('calendar.no-masters', compact('selectedDate'))->with('warning', 'В системе нет активных мастеров.');
            }
        }
        
        // Фильтруем мастеров - показываем только тех, кто работает в выбранную дату
        $masterIds = $masters->pluck('id')->filter();
        $userIds = $masters->pluck('user_id')->filter();
        
        $schedules = Schedule::where(function($query) use ($masterIds, $userIds) {
                $query->whereIn('master_id', $masterIds)
                      ->orWhereIn('user_id', $userIds);
            })
            ->whereDate('date', $selectedDate)
            ->get();
            
        $workingMasters = $masters->filter(function($master) use ($schedules) {
            $masterSchedule = $schedules->where('master_id', $master->id)->first() 
                ?? $schedules->where('user_id', $master->user_id)->first();
            // Показываем мастера, если у него есть расписание и он не на выходном
            return $masterSchedule && !$masterSchedule->is_day_off;
        });
        
        // Если нет работающих мастеров, показываем сообщение
        if ($workingMasters->count() == 0) {
            // Если мастера есть в системе, но никто не работает в этот день
            if ($masters->count() > 0) {
                return view('calendar.no-masters', compact('selectedDate'))->with('warning', 'В выбранную дату никто из мастеров не работает. Выберите другую дату.');
            } else {
                // Если мастеров вообще нет в системе
                return view('calendar.no-masters', compact('selectedDate'))->with('warning', 'В системе нет активных мастеров.');
            }
        }
        
        $masters = $workingMasters;
        
        // Получаем только услуги текущего пользователя
        $services = Service::forUser($user->id)->where('is_active', true)->get();
        
        // Получаем только клиентов текущего пользователя
        $clients = Client::forUser($user->id)->orderBy('name')->get();
        
        return view('calendar.create-appointment', compact(
            'date', 
            'time', 
            'masterId', 
            'masters', 
            'services', 
            'clients'
        ));
    }

    /**
     * Store a new appointment from calendar
     */
    public function storeAppointment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Разрешаем "new" для нового клиента или проверяем существование ID
                    if ($value !== 'new' && !Client::where('id', $value)->where('user_id', Auth::id())->exists()) {
                        $fail('Выберите клиента или создайте нового.');
                    }
                },
            ],
            'master_id' => 'required|exists:masters,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'time' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$value || !$request->start_time) {
                        return;
                    }
                    
                    try {
                        $startTime = $request->start_time;
                        $endTime = $value;
                        
                        // Parse times
                        [$startHour, $startMin] = explode(':', $startTime);
                        [$endHour, $endMin] = explode(':', $endTime);
                        
                        $startMinutes = (int)$startHour * 60 + (int)$startMin;
                        $endMinutes = (int)$endHour * 60 + (int)$endMin;
                        
                        // Handle next day case
                        if ($endMinutes <= $startMinutes) {
                            $endMinutes += 24 * 60; // Add 24 hours
                        }
                        
                        $duration = $endMinutes - $startMinutes;
                        
                        // Check minimum duration (15 minutes)
                        if ($duration < 15) {
                            $fail('Минимальная длительность записи 15 минут.');
                        }
                        
                        // Check maximum duration (8 hours = 480 minutes)
                        if ($duration > 480) {
                            $fail('Длительность записи не может превышать 8 часов.');
                        }
                        
                    } catch (\Exception $e) {
                        $fail('Неверный формат времени.');
                    }
                },
            ],
            'notes' => 'nullable|string|max:1000',
            // New client fields - требуются только при выборе "new"
            'client_name' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->client_id === 'new' && !$value) {
                        $fail('Имя клиента обязательно для заполнения.');
                    }
                },
                'nullable',
                'string',
                'max:255'
            ],
            'client_phone' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->client_id === 'new') {
                        if (!$value) {
                            $fail('Телефон клиента обязателен для заполнения.');
                            return;
                        }
                        // Проверяем уникальность телефона только среди клиентов текущего пользователя
                        if (Client::where('phone', $value)->where('user_id', Auth::id())->exists()) {
                            $fail('Клиент с таким телефоном уже существует у вас');
                        }
                    }
                },
                'nullable',
                'string',
                'max:20'
            ],
        ], [
            'client_id.required' => 'Выберите клиента или создайте нового.',
            'master_id.required' => 'Выберите мастера.',
            'service_id.required' => 'Выберите услугу.',
            'date.required' => 'Дата обязательна.',
            'start_time.required' => 'Время начала обязательно.',
            'end_time.required' => 'Время окончания обязательно.',
            'end_time.date_format' => 'Неверный формат времени окончания.',
        ]);

        // Handle client creation or selection
        $clientId = $validated['client_id'] ?? null;
        if (!$clientId) {
            return back()->withErrors(['client_id' => 'Выберите клиента или создайте нового.'])->withInput();
        }
        
        if ($clientId === 'new') {
            // Создаем нового клиента
            // Проверяем, существует ли клиент с таким телефоном у текущего пользователя
            $existingClient = Client::where('phone', $validated['client_phone'])
                ->where('user_id', Auth::id())
                ->first();
                
            if ($existingClient) {
                return back()->withErrors(['client_phone' => 'Клиент с таким телефоном уже существует у вас.']);
            }
            
            $client = Client::create([
                'name' => $validated['client_name'],
                'phone' => $validated['client_phone'],
                'user_id' => Auth::id(),
            ]);
            $clientId = $client->id;
        }

        // Build start and end datetime
        $startDateTime = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $endDateTime = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
        
        // Handle case where end time is next day
        if ($endDateTime <= $startDateTime) {
            $endDateTime->addDay();
        }

        // Validation: check if master is working
        $master = Master::find($validated['master_id']);
        
        // Try to find schedule by master_id first, then by user_id
        $schedule = Schedule::where('master_id', $master->id)
            ->whereDate('date', $startDateTime->toDateString())
            ->first();
            
        if (!$schedule && $master->user_id) {
            $schedule = Schedule::where('user_id', $master->user_id)
                ->whereDate('date', $startDateTime->toDateString())
                ->first();
        }
        
        // If still no schedule, try to find user by email and get schedule
        if (!$schedule) {
            $user = User::where('email', $master->email)->first();
            if ($user) {
                $schedule = Schedule::where('user_id', $user->id)
                    ->whereDate('date', $startDateTime->toDateString())
                    ->first();
            }
        }

        if (!$schedule || $schedule->is_day_off) {
            return back()->withErrors(['start_time' => 'Мастер не работает в этот день']);
        }

        $scheduleStart = Carbon::parse($schedule->start_time);
        $scheduleEnd = Carbon::parse($schedule->end_time);

        // Check if appointment times are within master's working hours
        if ($startDateTime->format('H:i') < $scheduleStart->format('H:i') || 
            $endDateTime->format('H:i') > $scheduleEnd->format('H:i')) {
            return back()->withErrors(['start_time' => 'Время записи должно быть в рамках рабочего времени мастера (' . $scheduleStart->format('H:i') . ' - ' . $scheduleEnd->format('H:i') . ')']);
        }

        // Check for overlapping appointments
        $overlappingAppointment = Appointment::where('master_id', $master->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function ($q) use ($startDateTime, $endDateTime) {
                    // New appointment starts before existing ends AND new appointment ends after existing starts
                    $q->where('start_time', '<', $endDateTime)
                      ->where('end_time', '>', $startDateTime);
                });
            })
            ->first();

        if ($overlappingAppointment) {
            return back()->withErrors(['start_time' => 'На это время уже есть запись']);
        }

        // Определяем цену услуги (учитываем индивидуальную цену мастера, если задана)
        $service = Service::find($validated['service_id']);
        $price = $service ? $service->price : 0;

        // Проверяем индивидуальные настройки цены на связке мастер-услуга
        if ($master) {
            $masterService = $master->services()->where('service_id', $validated['service_id'])->first();
            if ($masterService && !is_null($masterService->pivot->custom_price)) {
                $price = $masterService->pivot->custom_price;
            }
        }

        // Create the appointment
        $appointment = Appointment::create([
            'client_id' => $clientId,
            'master_id' => $validated['master_id'],
            'service_id' => $validated['service_id'],
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'price' => $price,
            'status' => 'scheduled',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Redirect back to calendar day view
        return redirect()->route('calendar.day', ['date' => $validated['date']])
            ->with('success', 'Запись успешно создана');
    }
}
