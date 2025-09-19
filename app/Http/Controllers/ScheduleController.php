<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Master;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $currentDate = Carbon::now();
        
        // Get current month or specified month
        $month = $request->get('month', $currentDate->month);
        $year = $request->get('year', $currentDate->year);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Calculate actual calendar range (including days from previous/next months)
        $calendarStart = $startDate->copy()->startOfWeek();
        $calendarEnd = $endDate->copy()->endOfWeek();
        
        // Get schedules for the entire calendar view (all visible days)
        $schedules = Schedule::forUser($user->id)
            ->dateRange($calendarStart, $calendarEnd)
            ->orderBy('date')
            ->get()
            ->keyBy(function ($schedule) {
                return $schedule->date->format('Y-m-d');
            });
        
        // Generate calendar data
        $calendar = $this->generateCalendarData($startDate, $endDate, $schedules);
        
        return view('schedules.index', compact('calendar', 'schedules', 'startDate', 'endDate'));
    }

    /**
     * Show the form for creating a new schedule.
     */
    public function create(Request $request): View
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        return view('schedules.create', compact('date'));
    }

    /**
     * Store a newly created schedule.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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

        // Check if schedule already exists for this date
        $existingSchedule = Schedule::forUser($request->user()->id)
            ->forDate($validated['date'])
            ->first();

        if ($existingSchedule) {
            return back()->withErrors(['date' => 'Расписание на эту дату уже существует.']);
        }

        // Create main schedule
        $schedule = $this->createSchedule($request->user(), $validated);

        // Create recurring schedules if needed
        if (isset($validated['is_recurring']) && $validated['is_recurring'] && $validated['recurring_until']) {
            $this->createRecurringSchedules($request->user(), $validated);
        }

        return redirect()->route('schedules.index')
            ->with('success', 'Расписание успешно создано!');
    }



    /**
     * Show the form for editing the specified schedule.
     */
    public function edit(Schedule $schedule): View
    {
        $this->authorize('update', $schedule);
        
        return view('schedules.edit', compact('schedule'));
    }

    /**
     * Update the specified schedule.
     */
    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->authorize('update', $schedule);

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

        try {
            $schedule->update($validated);

            return redirect()->route('schedules.index')
                ->with('success', 'Расписание успешно обновлено!');
        } catch (\Exception $e) {
            \Log::error('Error updating schedule:', ['error' => $e->getMessage()]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Произошла ошибка при сохранении расписания: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);

        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Расписание успешно удалено!');
    }

    /**
     * Get available slots for a specific date.
     */
    public function availableSlots(Request $request)
    {
        $date = $request->get('date');
        $duration = $request->get('duration', 60);
        
        $schedule = Schedule::forUser($request->user()->id)
            ->forDate($date)
            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'Расписание не найдено'], 404);
        }

        $slots = $schedule->getAvailableSlots($duration);

        return response()->json($slots);
    }

    /**
     * Create a single schedule.
     */
    private function createSchedule($user, $data)
    {
        $isDayOff = isset($data['is_day_off']) ? (bool)$data['is_day_off'] : false;
        
        // Получаем ID мастера, если пользователь - мастер
        $masterId = null;
        if ($user->isMaster()) {
            // Пытаемся найти запись мастера по user_id
            $master = Master::where('user_id', $user->id)->first();
            if ($master) {
                $masterId = $master->id;
            }
        }
        
        return Schedule::create([
            'user_id' => $user->id,
            'master_id' => $masterId,
            'date' => $data['date'],
            'is_day_off' => $isDayOff,
            'start_time' => $isDayOff ? null : ($data['start_time'] ?? null),
            'end_time' => $isDayOff ? null : ($data['end_time'] ?? null),
            'notes' => $data['notes'] ?? null,
            'is_recurring' => isset($data['is_recurring']) ? (bool)$data['is_recurring'] : false,
            'recurring_until' => $data['recurring_until'] ?? null,
        ]);
    }

    /**
     * Create recurring schedules.
     */
    private function createRecurringSchedules($user, $data)
    {
        $startDate = Carbon::parse($data['date'])->addDay();
        $endDate = Carbon::parse($data['recurring_until']);
        $dayOfWeek = Carbon::parse($data['date'])->dayOfWeek;

        $current = $startDate->copy();
        while ($current <= $endDate) {
            if ($current->dayOfWeek === $dayOfWeek) {
                // Check if schedule doesn't already exist
                $exists = Schedule::forUser($user->id)
                    ->forDate($current->format('Y-m-d'))
                    ->exists();

                if (!$exists) {
                    $this->createSchedule($user, array_merge($data, [
                        'date' => $current->format('Y-m-d')
                    ]));
                }
            }
            $current->addDay();
        }
    }

    /**
     * Generate calendar data for the view.
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
}
