<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'notes',
        'user_id',
        'email',
        'birth_date',
        'address',
        'preferred_communication',
        'language',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'birth_date' => 'date',
    ];

    /**
     * Получить записи этого клиента
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
    
    /**
     * Связь с пользователем, которому принадлежит клиент
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Получить клиентов определенного пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Получить последние записи клиента
     */
    public function recentAppointments($limit = 5)
    {
        return $this->appointments()
            ->with(['master', 'service'])
            ->orderBy('start_time', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Получить список предстоящих записей клиента
     */
    public function upcomingAppointments()
    {
        return $this->appointments()
            ->with(['master', 'service'])
            ->where('start_time', '>', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time', 'asc')
            ->get();
    }
} 