<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'master_id',
        'service_id',
        'start_time',
        'end_time',
        'price',
        'status',
        'is_confirmed',
        'is_paid',
        'payment_method',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_confirmed' => 'boolean',
        'is_paid' => 'boolean',
    ];
    
    /**
     * Возможные статусы записи
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    /**
     * Получить клиента для этой записи
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Получить мастера для этой записи
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    /**
     * Получить услугу для этой записи
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    
    /**
     * Получить пользователя, создавшего запись
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Получить предстоящие записи
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
            ->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_COMPLETED, self::STATUS_NO_SHOW]);
    }
    
    /**
     * Получить завершенные записи
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Получить записи для определенного мастера
     */
    public function scopeForMaster($query, $masterId)
    {
        return $query->where('master_id', $masterId);
    }

    /**
     * Получить записи для определенного клиента
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
    
    /**
     * Получить записи для определённого дня
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('start_time', $date);
    }
    
    /**
     * Получить длительность записи в минутах
     */
    public function getDurationInMinutesAttribute()
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }
    
    /**
     * Получить форматированную дату записи
     */
    public function getFormattedDateAttribute()
    {
        return $this->start_time->format('d.m.Y');
    }
    
    /**
     * Получить форматированное время записи
     */
    public function getFormattedTimeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
} 