<?php

namespace App\Services;

use App\Models\ReminderTemplate;
use App\Models\Appointment;
use Illuminate\Support\Str;

class TemplateProcessor
{
    /**
     * Обработать шаблон с данными из записи
     */
    public function processTemplate(ReminderTemplate $template, Appointment $appointment): string
    {
        $processedText = $this->replacePlaceholders($template->body, $appointment);
        
        // Дополнительная обработка с MCP Context7 (если необходимо)
        $processedText = $this->enhanceWithMCP($processedText, $appointment);
        
        return $processedText;
    }

    /**
     * Заменить плейсхолдеры в тексте на данные из записи
     */
    protected function replacePlaceholders(string $text, Appointment $appointment): string
    {
        $placeholders = $this->getPlaceholders($appointment);
        
        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $text
        );
    }

    /**
     * Получить массив плейсхолдеров и их значений
     */
    protected function getPlaceholders(Appointment $appointment): array
    {
        return [
            '{client_name}' => $appointment->client->name ?? 'Не указано',
            '{service_name}' => $appointment->service->name ?? 'Не указано',
            '{date_time}' => $appointment->start_time?->format('d.m.Y в H:i') ?? 'Не указано',
            '{price}' => $appointment->price ? $this->formatPrice($appointment->price) : 'Не указано',
            '{studio_address}' => $this->getStudioAddress($appointment),
            '{master_name}' => $appointment->master?->full_name ?? 'Не указано',
            '{duration}' => $this->formatDuration($appointment),
            '{phone}' => $this->getMasterPhone($appointment),
        ];
    }

    /**
     * Получить адрес студии/салона
     */
    protected function getStudioAddress(Appointment $appointment): string
    {
        // Если запись создана мастером, используем его адрес
        if ($appointment->master && $appointment->master->user) {
            return $appointment->master->user->address ?? 'Не указано';
        }
        
        // Если запись создана салоном, используем адрес салона
        if ($appointment->createdBy && $appointment->createdBy->isSalon()) {
            return $appointment->createdBy->address ?? 'Не указано';
        }
        
        return 'Не указано';
    }

    /**
     * Получить телефон мастера
     */
    protected function getMasterPhone(Appointment $appointment): string
    {
        if ($appointment->master && $appointment->master->user) {
            return $appointment->master->user->phone ?? 'Не указано';
        }
        
        return 'Не указано';
    }

    /**
     * Форматировать цену
     */
    protected function formatPrice(float $price): string
    {
        return number_format($price, 0, ',', ' ') . ' euro.';
    }

    /**
     * Форматировать длительность
     */
    protected function formatDuration(Appointment $appointment): string
    {
        if ($appointment->start_time && $appointment->end_time) {
            $minutes = $appointment->start_time->diffInMinutes($appointment->end_time);
            
            if ($minutes >= 60) {
                $hours = intval($minutes / 60);
                $remainingMinutes = $minutes % 60;
                
                if ($remainingMinutes > 0) {
                    return $hours . ' ч. ' . $remainingMinutes . ' мин.';
                } else {
                    return $hours . ' ч.';
                }
            } else {
                return $minutes . ' мин.';
            }
        }
        
        return 'Не указано';
    }

    /**
     * Валидация шаблона на наличие поддерживаемых плейсхолдеров
     */
    public function validateTemplate(string $templateBody): array
    {
        $errors = [];
        $supportedPlaceholders = [
            '{client_name}',
            '{service_name}',
            '{date_time}',
            '{price}',
            '{studio_address}',
            '{master_name}',
            '{duration}',
            '{phone}',
        ];

        // Найти все плейсхолдеры в шаблоне
        preg_match_all('/\{[^}]+\}/', $templateBody, $matches);
        
        foreach ($matches[0] as $placeholder) {
            if (!in_array($placeholder, $supportedPlaceholders)) {
                $errors[] = "Неподдерживаемый плейсхолдер: {$placeholder}";
            }
        }

        return $errors;
    }

    /**
     * Получить список всех доступных плейсхолдеров
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            '{client_name}' => 'Имя клиента',
            '{service_name}' => 'Название услуги',
            '{date_time}' => 'Дата и время записи',
            '{price}' => 'Стоимость услуги',
            '{studio_address}' => 'Адрес студии/салона',
            '{master_name}' => 'Имя мастера',
            '{duration}' => 'Длительность услуги',
            '{phone}' => 'Телефон мастера',
        ];
    }

    /**
     * Генерировать превью шаблона с тестовыми данными
     */
    public function generatePreview(string $templateBody): string
    {
        $testData = [
            '{client_name}' => 'Николь',
            '{service_name}' => 'Маникюр',
            '{date_time}' => '15.07.2025 в 14:30',
            '{price}' => '25 euro',
            '{studio_address}' => 'ул. Красная, 15',
            '{master_name}' => 'Николь Мирзоева',
            '{duration}' => '1 ч. 30 мин.',
            '{phone}' => '+371 23456789',
        ];

        return str_replace(
            array_keys($testData),
            array_values($testData),
            $templateBody
        );
    }
} 