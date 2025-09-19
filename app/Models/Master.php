<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Master extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'bio',
        'photo_path',
        'is_active',
        'salon_id',
        'user_id',
        'specialization',
        'experience_years',
        'certificates',
        'rating',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'certificates' => 'array',
        'experience_years' => 'integer',
        'rating' => 'decimal:2',
    ];

    /**
     * Получить записи этого мастера
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Получить услуги, которые оказывает мастер
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->withPivot(['custom_price', 'custom_duration'])
            ->withTimestamps();
    }

    /**
     * Получить расписание мастера
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
    
    /**
     * Связь с салоном (старая связь один-ко-многим)
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salon_id');
    }
    
    /**
     * Связь с салонами (новая связь многие-ко-многим)
     */
    public function salons(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'salon_master', 'master_id', 'salon_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Связь с пользователем-мастером
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Получить список активных мастеров
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Получить мастеров определенного салона (старый метод)
     */
    public function scopeForSalon($query, $salonId)
    {
        return $query->where('salon_id', $salonId);
    }
    
    /**
     * Получить мастеров определенного салона через связь многие-ко-многим
     */
    public function scopeForSalonRelation($query, $salonId)
    {
        return $query->whereHas('salons', function($q) use ($salonId) {
            $q->where('users.id', $salonId);
        });
    }
    
    /**
     * Получить имя мастера, для вывода на фронтенде
     */
    public function getFullNameAttribute()
    {
        // Если мастер связан с пользователем, используем имя оттуда,
        // иначе используем имя из таблицы мастеров
        if ($this->user) {
            return $this->user->name;
        }
        
        return $this->name;
    }
} 