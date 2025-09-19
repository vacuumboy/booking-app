<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_type',
        'avatar',
        'bio',
        'address',
        'is_active',
        'is_verified',
        'photo_path',
        'salon_name',
        'specialization',
        'experience_years',
        'working_hours',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'working_hours' => 'array',
        ];
    }

    /**
     * Проверяет, является ли пользователь мастером
     */
    public function isMaster()
    {
        return $this->user_type === 'master';
    }

    /**
     * Проверяет, является ли пользователь салоном
     */
    public function isSalon()
    {
        return $this->user_type === 'salon';
    }

    /**
     * Проверяет, является ли пользователь клиентом
     */
    public function isClient()
    {
        return $this->user_type === 'client';
    }

    /**
     * Get the user's photo URL.
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=2563eb&background=e0f2fe';
    }

    /**
     * Получить профиль мастера, связанный с этим пользователем
     */
    public function masterProfile()
    {
        return $this->hasOne(Master::class);
    }

    /**
     * Получить профили мастеров, связанные с этим салоном (старый метод)
     */
    public function salonMasters()
    {
        if ($this->isSalon()) {
            return $this->hasMany(Master::class, 'salon_id');
        }
        return null;
    }

    /**
     * Получить профили мастеров, связанные с этим салоном через связь многие-ко-многим
     */
    public function masters()
    {
        if ($this->isSalon()) {
            return $this->belongsToMany(Master::class, 'salon_master', 'salon_id', 'master_id')
                ->withPivot('is_active')
                ->withTimestamps();
        }
        return null;
    }

    /**
     * Get clients for this user.
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get services for this user (salon).
     */
    public function services()
    {
        if ($this->isSalon()) {
            return $this->hasMany(Service::class);
        }
        return null;
    }
    
    /**
     * Get appointments created by this user.
     */
    public function createdAppointments()
    {
        return $this->hasMany(Appointment::class, 'created_by_user_id');
    }

    /**
     * Get reminder templates for this user.
     */
    public function reminderTemplates()
    {
        return $this->hasMany(ReminderTemplate::class);
    }

    /**
     * Get active reminder templates for this user.
     */
    public function activeReminderTemplates()
    {
        return $this->reminderTemplates()->active();
    }
}
