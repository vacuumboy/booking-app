<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем пользователя-салон
        $salon = User::where('user_type', 'salon')->first();
        
        if (!$salon) {
            $this->command->error('Не найден пользователь типа "salon"');
            return;
        }
        
        $services = [
            [
                'name' => 'Маникюр классический',
                'description' => 'Классический маникюр с обработкой кутикулы',
                'price' => 1500,
                'duration' => 60,
                'category_name' => 'Маникюр',
                'user_id' => $salon->id,
                'is_active' => true,
            ],
            [
                'name' => 'Маникюр с покрытием гель-лак',
                'description' => 'Маникюр с нанесением гель-лака и дизайном',
                'price' => 2500,
                'duration' => 90,
                'category_name' => 'Маникюр',
                'user_id' => $salon->id,
                'is_active' => true,
            ],
            [
                'name' => 'Педикюр классический',
                'description' => 'Классический педикюр с обработкой стоп',
                'price' => 2000,
                'duration' => 60,
                'category_name' => 'Педикюр',
                'user_id' => $salon->id,
                'is_active' => true,
            ],
            [
                'name' => 'Наращивание ногтей',
                'description' => 'Наращивание ногтей гелем с покрытием',
                'price' => 3500,
                'duration' => 120,
                'category_name' => 'Наращивание',
                'user_id' => $salon->id,
                'is_active' => true,
            ],
            [
                'name' => 'Дизайн ногтей',
                'description' => 'Художественное оформление ногтей',
                'price' => 500,
                'duration' => 30,
                'category_name' => 'Дизайн',
                'user_id' => $salon->id,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
} 