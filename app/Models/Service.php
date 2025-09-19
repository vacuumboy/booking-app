<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ru',
        'name_lv',
        'name_en',
        'description',
        'price',
        'duration',
        'color',
        'color_code',
        'requirements',
        'is_active',
        'user_id'
    ];

    protected $casts = [
        'price' => 'float',
        'duration' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Получить мастеров, которые выполняют эту услугу
     */
    public function masters(): BelongsToMany
    {
        return $this->belongsToMany(Master::class)
            ->withPivot(['custom_price', 'custom_duration'])
            ->withTimestamps();
    }

    /**
     * Получить записи на эту услугу
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
    
    /**
     * Связь с пользователем-салоном, которому принадлежит услуга
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Получить услуги определенного пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Получить только активные услуги
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Получить форматированную цену услуги
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', ' ') . ' €';
    }
    
    /**
     * Получить форматированную длительность услуги
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0) {
            return $hours . ' ч ' . ($minutes > 0 ? $minutes . ' мин' : '');
        }
        
        return $minutes . ' мин';
    }
    
    /**
     * Получить название услуги на указанном языке
     */
    public function getNameByLanguage($language = 'ru')
    {
        $languageField = "name_{$language}";
        
        // Проверяем, существует ли поле для данного языка и есть ли значение
        if (isset($this->$languageField) && !empty($this->$languageField)) {
            return $this->$languageField;
        }
        
        // Если нет перевода на нужный язык, используем русский как fallback
        if (!empty($this->name_ru)) {
            return $this->name_ru;
        }
        
        // Если и русского нет, используем основное поле name
        return $this->name;
    }
} 