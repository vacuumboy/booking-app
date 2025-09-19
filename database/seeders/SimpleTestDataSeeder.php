<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Master;
use App\Models\Service;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SimpleTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Отключаем проверку внешних ключей для SQLite
        DB::statement('PRAGMA foreign_keys = OFF');

        // Создаем пользователя-салон
        $salon = User::create([
            'name' => 'Тестовый салон',
            'email' => 'salon@example.com',
            'phone' => '+7 (999) 123-45-67',
            'address' => 'ул. Тестовая, 123',
            'password' => Hash::make('password'),
            'user_type' => 'salon',
            'is_active' => true,
        ]);

        // Создаем пользователя-мастера
        $masterUser = User::create([
            'name' => 'Тестовый мастер',
            'email' => 'master@example.com',
            'phone' => '+7 (999) 765-43-21',
            'address' => 'ул. Мастеров, 456',
            'password' => Hash::make('password'),
            'user_type' => 'master',
            'is_active' => true,
        ]);

        // Создаем запись мастера
        $master = Master::create([
            'name' => 'Тестовый мастер',
            'email' => 'master@example.com',
            'phone' => '+7 (999) 765-43-21',
            'bio' => 'Опытный мастер с большим стажем работы',
            'is_active' => true,
            'user_id' => $masterUser->id,
        ]);

        // Создаем связь между салоном и мастером
        DB::table('salon_master')->insert([
            'salon_id' => $salon->id,
            'master_id' => $master->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Создаем второго мастера
        $master2User = User::create([
            'name' => 'Второй мастер',
            'email' => 'master2@example.com',
            'phone' => '+7 (999) 222-33-44',
            'address' => 'ул. Мастеров, 789',
            'password' => Hash::make('password'),
            'user_type' => 'master',
            'is_active' => true,
        ]);

        $master2 = Master::create([
            'name' => 'Второй мастер',
            'email' => 'master2@example.com',
            'phone' => '+7 (999) 222-33-44',
            'bio' => 'Молодой талантливый мастер',
            'is_active' => true,
            'user_id' => $master2User->id,
        ]);

        // Создаем связь между салоном и вторым мастером
        DB::table('salon_master')->insert([
            'salon_id' => $salon->id,
            'master_id' => $master2->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Создаем несколько услуг
        $services = [
            [
                'name' => 'Стрижка',
                'description' => 'Базовая стрижка',
                'price' => 1000,
                'duration' => 60,
                'category_name' => 'Волосы',
                'is_active' => true,
                'user_id' => $salon->id,
            ],
            [
                'name' => 'Окрашивание',
                'description' => 'Окрашивание волос',
                'price' => 2500,
                'duration' => 120,
                'category_name' => 'Волосы',
                'is_active' => true,
                'user_id' => $salon->id,
            ],
            [
                'name' => 'Маникюр',
                'description' => 'Классический маникюр',
                'price' => 1500,
                'duration' => 90,
                'category_name' => 'Ногти',
                'is_active' => true,
                'user_id' => $salon->id,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        // Создаем клиента
        Client::create([
            'name' => 'Николь',
            'phone' => '+7 (999) 111-22-33',
            'email' => 'client@example.com',
            'user_id' => $salon->id,
        ]);

        // Включаем проверку внешних ключей
        DB::statement('PRAGMA foreign_keys = ON');
    }
}
