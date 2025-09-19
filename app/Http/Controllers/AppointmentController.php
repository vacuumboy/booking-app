<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\Service;
use App\Models\Client;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login');
            }
            
            // Получаем записи с учетом типа пользователя
            $query = Appointment::with(['client', 'master', 'service']);
            
            // Для мастеров показываем только их записи
            if ($user->isMaster()) {
                $masters = Master::where('user_id', $user->id)->get();
                if ($masters->count() == 0) {
                    $masters = Master::where('email', $user->email)->get();
                }
                $masterIds = $masters->pluck('id')->toArray();
                if (!empty($masterIds)) {
                    $query->whereIn('master_id', $masterIds);
                }
            }
            // Для салонов показываем записи их мастеров
            elseif ($user->isSalon()) {
                $masters = $user->masters();
                if ($masters) {
                    $masterIds = $masters->pluck('masters.id')->toArray();
                    if (!empty($masterIds)) {
                        $query->whereIn('master_id', $masterIds);
                    }
                }
            }

        // Применяем фильтры
        if ($request->has('master_id') && $request->master_id) {
            $query->where('master_id', $request->master_id);
        }

        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('date') && $request->date) {
            $date = Carbon::parse($request->date);
            $query->whereDate('start_time', $date);
        }

        $appointments = $query->orderBy('start_time', 'desc')->paginate(15);
        
        // Получаем данные для фильтров
        $masters = collect();
        $clients = Client::forUser($user->id)->orderBy('name')->get();
        
        if ($user->isMaster()) {
            $masters = Master::where('user_id', $user->id)->get();
            if ($masters->count() == 0) {
                $masters = Master::where('email', $user->email)->get();
            }
        } elseif ($user->isSalon()) {
            $masters = $user->masters;
        } else {
            $masters = Master::where('is_active', true)->get();
        }

        return view('appointments.index', compact('appointments', 'masters', 'clients'));
        } catch (\Exception $e) {
            \Log::error('Error in appointments index: ' . $e->getMessage());
            return view('appointments.index', [
                'appointments' => collect(),
                'masters' => collect(),
                'clients' => collect()
            ]);
        }
    }

    public function store(Request $request)
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
            'master_id.required' => 'Выберите мастера.',
            'service_id.required' => 'Выберите услугу.',
            'date.required' => 'Дата обязательна.',
            'start_time.required' => 'Время начала обязательно.',
            'end_time.required' => 'Время окончания обязательно.',
            'end_time.date_format' => 'Неверный формат времени окончания.',
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
                // Если клиент существует, используем его
                $clientId = $existingClient->id;
            } else {
                // Create new client
                $client = Client::create([
                    'name' => $validated['client_name'],
                    'phone' => $validated['client_phone'],
                    'user_id' => Auth::id(), // Добавляем ID текущего пользователя
                ]);
                $clientId = $client->id;
            }
        }

        if (!$clientId || $clientId === 'new') {
            return back()->withErrors(['client_id' => 'Выберите клиента или создайте нового']);
        }
        
        // Проверяем, принадлежит ли клиент текущему пользователю
        $client = Client::find($clientId);
        if ($client->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому клиенту');
        }
        
        // Проверяем, принадлежит ли услуга текущему пользователю
        $service = Service::find($validated['service_id']);
        if ($service->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этой услуге');
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

        if ($startDateTime->format('H:i') < $scheduleStart->format('H:i') || 
            $endDateTime->format('H:i') > $scheduleEnd->format('H:i')) {
            return back()->withErrors(['start_time' => 'Время записи вне рабочего графика мастера (' . $schedule->start_time . ' - ' . $schedule->end_time . ')']);
        }

        // Check for conflicting appointments (excluding cancelled)
        $conflictingAppointment = Appointment::where('master_id', $validated['master_id'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<=', $startDateTime)
                      ->where('end_time', '>', $startDateTime);
                })->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<', $endDateTime)
                      ->where('end_time', '>=', $endDateTime);
                })->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '>=', $startDateTime)
                      ->where('end_time', '<=', $endDateTime);
                });
            })
            ->exists();

        if ($conflictingAppointment) {
            return back()->withErrors(['start_time' => 'Выбранное время уже занято']);
        }

        $appointment = Appointment::create([
            'client_id' => $clientId,
            'master_id' => $validated['master_id'],
            'service_id' => $validated['service_id'],
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'price' => $service->price,
            'notes' => $validated['notes'] ?? null,
            'status' => 'confirmed',
        ]);

        return redirect()->route('calendar.day', ['date' => $validated['date']])
            ->with('success', 'Запись успешно создана!');
    }

    public function edit(Appointment $appointment)
    {
        // Загружаем связанные данные
        $appointment->load(['client', 'master', 'service']);
        
        $user = request()->user();
        
        // Получаем мастеров в зависимости от типа пользователя
        if ($user->isMaster()) {
            $masters = Master::where('user_id', $user->id)->get();
            if ($masters->count() == 0) {
                $masters = Master::where('email', $user->email)->get();
            }
        } else if ($user->isSalon()) {
            $masters = $user->masters()->get();
        } else {
            $masters = Master::where('is_active', true)->get();
        }
        
        // Получаем услуги и клиентов текущего пользователя
        $services = Service::forUser($user->id)->where('is_active', true)->get();
        $clients = Client::forUser($user->id)->orderBy('name')->get();
        
        return view('appointments.edit', compact(
            'appointment', 
            'masters', 
            'services', 
            'clients'
        ));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'master_id' => 'required|exists:masters,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
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
        ], [
            'client_id.required' => 'Выберите клиента.',
            'master_id.required' => 'Выберите мастера.',
            'service_id.required' => 'Выберите услугу.',
            'date.required' => 'Дата обязательна.',
            'start_time.required' => 'Время начала обязательно.',
            'end_time.required' => 'Время окончания обязательно.',
        ]);

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

        if ($startDateTime->format('H:i') < $scheduleStart->format('H:i') || 
            $endDateTime->format('H:i') > $scheduleEnd->format('H:i')) {
            return back()->withErrors(['start_time' => 'Время записи вне рабочего графика мастера (' . $schedule->start_time . ' - ' . $schedule->end_time . ')']);
        }

        // Check for conflicting appointments (excluding current one and cancelled)
        $conflictingAppointment = Appointment::where('master_id', $validated['master_id'])
            ->where('id', '!=', $appointment->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<=', $startDateTime)
                      ->where('end_time', '>', $startDateTime);
                })->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<', $endDateTime)
                      ->where('end_time', '>=', $endDateTime);
                })->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '>=', $startDateTime)
                      ->where('end_time', '<=', $endDateTime);
                });
            })
            ->exists();

        if ($conflictingAppointment) {
            return back()->withErrors(['start_time' => 'Выбранное время уже занято']);
        }

        // Get service price
        $service = Service::find($validated['service_id']);

        $appointment->update([
            'client_id' => $validated['client_id'],
            'master_id' => $validated['master_id'],
            'service_id' => $validated['service_id'],
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'price' => $service->price,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('calendar.day', ['date' => $validated['date']])
            ->with('success', 'Запись успешно обновлена!');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(null, 204);
    }

    public function getAvailableSlots(Request $request)
    {
        $validated = $request->validate([
            'master_id' => 'required|exists:masters,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $master = Master::findOrFail($validated['master_id']);
        $service = Service::findOrFail($validated['service_id']);
        $date = Carbon::parse($validated['date']);

        $schedule = $master->schedules()
            ->whereDate('date', $date)
            ->first();

        if (!$schedule || $schedule->is_day_off) {
            return response()->json(['message' => 'Мастер не работает в этот день'], 422);
        }

        $workStart = Carbon::parse($schedule->start_time);
        $workEnd = Carbon::parse($schedule->end_time);

        $appointments = $master->appointments()
            ->whereDate('start_time', $date)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        $slots = [];
        $current = $workStart->copy();

        while ($current->copy()->addMinutes($service->duration) <= $workEnd) {
            $slotEnd = $current->copy()->addMinutes($service->duration);
            $isAvailable = true;

            foreach ($appointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->start_time);
                $appointmentEnd = Carbon::parse($appointment->end_time);

                if ($current->between($appointmentStart, $appointmentEnd) ||
                    $slotEnd->between($appointmentStart, $appointmentEnd) ||
                    ($current->lte($appointmentStart) && $slotEnd->gte($appointmentEnd))) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = [
                    'start_time' => $current->format('Y-m-d H:i:s'),
                    'end_time' => $slotEnd->format('Y-m-d H:i:s'),
                ];
            }

            $current->addMinutes(30); // Шаг в 30 минут
        }

        return response()->json($slots);
    }

    /**
     * Cancel the appointment
     */
    public function cancel(Appointment $appointment)
    {
        // Проверяем, что запись не уже отменена
        if ($appointment->status === 'cancelled') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Запись уже отменена'
                ], 400);
            }
            return redirect()->back()->with('error', 'Запись уже отменена');
        }

        // Отменяем запись
        $appointment->update([
            'status' => 'cancelled'
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Запись успешно отменена'
            ]);
        }

        return redirect()->route('calendar.day', ['date' => $appointment->start_time->format('Y-m-d')])
            ->with('success', 'Запись успешно отменена');
    }
} 