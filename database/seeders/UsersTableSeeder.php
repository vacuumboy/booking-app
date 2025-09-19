<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем пользователя администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'phone' => '+7 999 111 22 33',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'avatar' => null,
            'bio' => 'Администратор системы',
            'address' => 'г. Москва',
            'is_active' => true,
            'is_verified' => true,
        ]);
        
        // Создаем пользователя-салон
        User::create([
            'name' => 'Салон Красоты "Престиж"',
            'email' => 'salon@example.com',
            'phone' => '+7 999 222 33 44',
            'password' => Hash::make('password'),
            'user_type' => 'salon',
            'avatar' => null,
            'bio' => 'Салон красоты премиум класса',
            'address' => 'ул. Красная, 15',
            'is_active' => true,
            'is_verified' => true,
            'salon_name' => 'Престиж',
            'working_hours' => [
                'mon' => ['09:00-21:00'],
                'tue' => ['09:00-21:00'],
                'wed' => ['09:00-21:00'],
                'thu' => ['09:00-21:00'],
                'fri' => ['09:00-21:00'],
                'sat' => ['10:00-20:00'],
                'sun' => ['10:00-19:00'],
            ],
        ]);
        
        // Создаем несколько пользователей-мастеров
        User::create([
            'name' => 'Николь Мирзоева',
            'email' => 'nicol@example.com',
            'phone' => '+7 999 333 44 55',
            'password' => Hash::make('password'),
            'user_type' => 'master',
            'avatar' => null,
            'bio' => 'Мастер маникюра с опытом работы более 5 лет',
            'address' => 'ул. Красная, 15',
            'is_active' => true,
            'is_verified' => true,
        ]);
        
        User::create([
            'name' => 'Петров Сергей',
            'email' => 'master2@example.com',
            'phone' => '+7 999 444 55 66',
            'password' => Hash::make('password'),
            'user_type' => 'master',
            'avatar' => null,
            'bio' => 'Парикмахер-стилист, колорист',
            'address' => 'г. Москва',
            'is_active' => true,
            'is_verified' => true,
        ]);
        
        // Создаем несколько обычных пользователей (клиентов)
        User::create([
            'name' => 'Сидоров Иван',
            'email' => 'client1@example.com',
            'phone' => '+7 999 555 66 77',
            'password' => Hash::make('password'),
            'user_type' => 'client',
            'avatar' => null,
            'bio' => '',
            'address' => 'г. Москва',
            'is_active' => true,
            'is_verified' => true,
        ]);
        
        User::create([
            'name' => 'Смирнова Екатерина',
            'email' => 'client2@example.com',
            'phone' => '+7 999 666 77 88',
            'password' => Hash::make('password'),
            'user_type' => 'client',
            'avatar' => null,
            'bio' => '',
            'address' => 'г. Москва',
            'is_active' => true,
            'is_verified' => true,
        ]);
        
        // Создаем еще несколько пользователей с помощью фабрики
        User::factory()
            ->count(3)
            ->state(['user_type' => 'master'])
            ->create();
            
        User::factory()
            ->count(5)
            ->state(['user_type' => 'client'])
            ->create();
            
        User::factory()
            ->count(2)
            ->state(['user_type' => 'salon'])
            ->create();
    }
}
