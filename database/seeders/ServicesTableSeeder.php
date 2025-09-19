<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех пользователей типа "салон"
        $salonUsers = User::where('user_type', 'salon')->get();
        
        if ($salonUsers->count() == 0) {
            // Если салонов нет, создаем один для демонстрации
            $salon = User::create([
                'name' => 'Демо Салон',
                'email' => 'demo.salon@example.com',
                'phone' => '+7 999 000 11 22',
                'password' => bcrypt('password'),
                'user_type' => 'salon',
                'is_active' => true,
                'is_verified' => true,
                'salon_name' => 'Демо',
            ]);
            
            $salonUsers->push($salon);
        }
        
        // Массивы услуг по категориям
        $services = [
            'Маникюр' => [
                [
                    'name' => 'Классический маникюр',
                    'description' => 'Обработка ногтей и кутикулы без покрытия',
                    'price' => 1000,
                    'duration' => 60,
                    'color_code' => '#FF9AA2'
                ],
                [
                    'name' => 'Маникюр с гель-лаком',
                    'description' => 'Классический маникюр с нанесением покрытия гель-лаком',
                    'price' => 2000,
                    'duration' => 90,
                    'color_code' => '#FFB7B2'
                ],
                [
                    'name' => 'Европейский маникюр',
                    'description' => 'Бережная обработка кутикулы без использования режущих инструментов',
                    'price' => 1500,
                    'duration' => 60,
                    'color_code' => '#FFDAC1'
                ],
            ],
            'Педикюр' => [
                [
                    'name' => 'Классический педикюр',
                    'description' => 'Обработка стоп и ногтей без покрытия',
                    'price' => 1500,
                    'duration' => 60,
                    'color_code' => '#E2F0CB'
                ],
                [
                    'name' => 'Педикюр с гель-лаком',
                    'description' => 'Классический педикюр с нанесением покрытия гель-лаком',
                    'price' => 2500,
                    'duration' => 90,
                    'color_code' => '#B5EAD7'
                ],
                [
                    'name' => 'Аппаратный педикюр',
                    'description' => 'Педикюр с использованием профессионального аппарата',
                    'price' => 2000,
                    'duration' => 60,
                    'color_code' => '#C7CEEA'
                ],
            ],
            'Стрижки' => [
                [
                    'name' => 'Женская стрижка короткие волосы',
                    'description' => 'Стрижка волос до плеч',
                    'price' => 1500,
                    'duration' => 60,
                    'color_code' => '#FF9AA2'
                ],
                [
                    'name' => 'Женская стрижка длинные волосы',
                    'description' => 'Стрижка волос ниже плеч',
                    'price' => 2000,
                    'duration' => 90,
                    'color_code' => '#FFB7B2'
                ],
                [
                    'name' => 'Мужская стрижка',
                    'description' => 'Классическая мужская стрижка',
                    'price' => 1200,
                    'duration' => 45,
                    'color_code' => '#FFDAC1'
                ],
            ],
            'Окрашивание' => [
                [
                    'name' => 'Окрашивание в один тон',
                    'description' => 'Однотонное окрашивание волос без предварительного осветления',
                    'price' => 3000,
                    'duration' => 120,
                    'color_code' => '#E2F0CB'
                ],
                [
                    'name' => 'Мелирование',
                    'description' => 'Частичное окрашивание прядей волос',
                    'price' => 4000,
                    'duration' => 150,
                    'color_code' => '#B5EAD7'
                ],
                [
                    'name' => 'Сложное окрашивание',
                    'description' => 'Сложные техники окрашивания (омбре, балаяж)',
                    'price' => 5000,
                    'duration' => 180,
                    'color_code' => '#C7CEEA'
                ],
            ],
            'Массаж' => [
                [
                    'name' => 'Классический массаж',
                    'description' => 'Общий расслабляющий массаж всего тела',
                    'price' => 3000,
                    'duration' => 60,
                    'color_code' => '#C7CEEA'
                ],
                [
                    'name' => 'Массаж спины',
                    'description' => 'Массаж спины и шейно-воротниковой зоны',
                    'price' => 2000,
                    'duration' => 30,
                    'color_code' => '#B5EAD7'
                ],
            ],
            'Косметология' => [
                [
                    'name' => 'Классическая чистка лица',
                    'description' => 'Механическая чистка лица',
                    'price' => 3500,
                    'duration' => 90,
                    'color_code' => '#FFDAC1'
                ],
                [
                    'name' => 'Массаж лица',
                    'description' => 'Омолаживающий массаж лица',
                    'price' => 2500,
                    'duration' => 45,
                    'color_code' => '#FFB7B2'
                ],
            ],
        ];
        
        // Создаем услуги для каждого салона
        foreach ($salonUsers as $salon) {
            foreach ($services as $category => $categoryServices) {
                foreach ($categoryServices as $serviceData) {
                    Service::create([
                        'name' => $serviceData['name'],
                        'description' => $serviceData['description'],
                        'price' => $serviceData['price'],
                        'duration' => $serviceData['duration'],
                        'category_name' => $category,
                        'color_code' => $serviceData['color_code'],
                        'is_active' => true,
                        'user_id' => $salon->id,
                    ]);
                }
            }
        }
        
        // Создаем несколько случайных услуг с помощью фабрики
        Service::factory()
            ->count(5)
            ->state(function () use ($salonUsers) {
                return [
                    'user_id' => $salonUsers->random()->id,
                    'category_name' => collect(['Маникюр', 'Педикюр', 'Стрижки', 'Окрашивание', 'Массаж', 'Косметология'])->random(),
                ];
            })
            ->create();
    }
}
