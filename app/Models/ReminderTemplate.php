<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'language',
        'body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить пользователя, которому принадлежит шаблон
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить только активные шаблоны
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить шаблоны для определенного пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Заменить плейсхолдеры в шаблоне на реальные данные
     */
    public function fillTemplate(Appointment $appointment): string
    {
        // Получаем язык клиента для правильного форматирования
        $clientLanguage = $appointment->client->language ?? 'ru';
        
        $placeholders = [
            '{client_name}' => $appointment->client->name ?? '',
            '{service_name}' => $appointment->service ? $appointment->service->getNameByLanguage($clientLanguage) : '',
            '{date_time}' => $this->formatDateTime($appointment->start_time, $clientLanguage),
            '{price}' => $appointment->price ? number_format($appointment->price, 0, ',', ' ') . ' euro' : '',
            '{studio_address}' => $this->getStudioAddress($appointment),
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $this->body);
    }
    
    /**
     * Форматировать дату и время в зависимости от языка
     */
    private function formatDateTime($dateTime, $language)
    {
        if (!$dateTime) return '';
        
        switch ($language) {
            case 'ru':
                return $dateTime->format('d.m.Y в H:i');
            case 'lv':
                return $dateTime->format('d.m.Y') . ' plkst. ' . $dateTime->format('H:i');
            case 'en':
                return $dateTime->format('d.m.Y') . ' at ' . $dateTime->format('H:i');
            default:
                return $dateTime->format('d.m.Y в H:i');
        }
    }

    /**
     * Получить адрес студии/салона для записи
     */
    private function getStudioAddress(Appointment $appointment): string
    {
        // Если запись создана мастером, используем его адрес
        if ($appointment->master && $appointment->master->user) {
            return $appointment->master->user->address ?? '';
        }
        
        // Если запись создана салоном, используем адрес салона
        if ($appointment->createdBy && $appointment->createdBy->isSalon()) {
            return $appointment->createdBy->address ?? '';
        }
        
        return '';
    }

    /**
     * Получить список доступных плейсхолдеров
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            '{client_name}' => 'Имя клиента',
            '{service_name}' => 'Название услуги',
            '{date_time}' => 'Дата и время записи',
            '{price}' => 'Стоимость услуги',
            '{studio_address}' => 'Адрес студии/салона',
        ];
    }
}
