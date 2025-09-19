<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'master_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'is_day_off',
        'notes',
        'is_recurring',
        'recurring_until',
        'breaks',
        'color_code',
        'is_visible_online',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'is_day_off' => 'boolean',
        'is_recurring' => 'boolean',
        'recurring_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'breaks' => 'json',
        'is_visible_online' => 'boolean',
    ];

    /**
     * Получить мастера, которому принадлежит расписание
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    /**
     * Получить записи для этого расписания
     */
    public function appointments()
    {
        // Получаем записи, которые попадают в этот день
        return Appointment::where('master_id', $this->master_id)
            ->whereDate('start_time', $this->date)
            ->get();
    }

    /**
     * Получить расписание для определенного мастера
     */
    public function scopeForMaster($query, $masterId)
    {
        return $query->where('master_id', $masterId);
    }

    /**
     * Получить расписание на определенную дату
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Получить только рабочие дни
     */
    public function scopeWorkingDays($query)
    {
        return $query->where('is_day_off', false);
    }

    /**
     * Получить расписание для определенного периода
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Получить расписание для определенного пользователя
     * (либо напрямую по user_id, либо через связь с мастером)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('master', function($query) use ($userId) {
                  $query->where('user_id', $userId);
              });
        });
    }

    /**
     * Получить рабочие часы в формате строки
     */
    public function getWorkingHoursAttribute()
    {
        if ($this->is_day_off) {
            return 'Выходной';
        }

        $start = $this->start_time;
        $end = $this->end_time;
        
        return "{$start}-{$end}";
    }

    /**
     * Проверить доступность расписания в определенное время
     */
    public function isAvailableAt($time)
    {
        if ($this->is_day_off) {
            return false;
        }

        $checkTime = Carbon::parse($time)->format('H:i');
        
        // Проверяем, входит ли время в рабочие часы
        if ($checkTime < $this->start_time || $checkTime > $this->end_time) {
            return false;
        }
        
        // Проверяем, не входит ли время в перерывы
        if ($this->breaks) {
            foreach ($this->breaks as $break) {
                if (isset($break['start']) && isset($break['end'])) {
                    if ($checkTime >= $break['start'] && $checkTime <= $break['end']) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Получить доступные временные слоты на день
     */
    public function getAvailableSlots($duration = 60)
    {
        if ($this->is_day_off) {
            return [];
        }

        $slots = [];
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $current = $start->copy();
        
        // Счетчик безопасности для предотвращения бесконечных циклов
        $maxIterations = 1000;
        $iterations = 0;

        while ($current->copy()->addMinutes($duration) <= $end && $iterations < $maxIterations) {
            $iterations++;
            $slotEnd = $current->copy()->addMinutes($duration);
            $isAvailable = true;
            
            // Проверяем, не попадает ли слот в перерывы
            if ($this->breaks) {
                foreach ($this->breaks as $break) {
                    if (isset($break['start']) && isset($break['end'])) {
                        $breakStart = Carbon::parse($break['start']);
                        $breakEnd = Carbon::parse($break['end']);
                        
                        // Если слот пересекается с перерывом
                        if ($current < $breakEnd && $slotEnd > $breakStart) {
                            $isAvailable = false;
                            break;
                        }
                    }
                }
            }
            
            // Проверяем, не занят ли слот другими записями
            $appointments = $this->appointments();
            foreach ($appointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->start_time);
                $appointmentEnd = Carbon::parse($appointment->end_time);
                
                // Если слот пересекается с записью
                if ($current < $appointmentEnd && $slotEnd > $appointmentStart) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($isAvailable) {
                $slots[] = [
                    'start' => $current->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'datetime' => $current->copy(),
                ];
            }

            $current->addMinutes(30); // 30-минутные интервалы
        }

        return $slots;
    }
} 