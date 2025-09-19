<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Master;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем тестового мастера
        $masterUser = User::create([
            'name' => 'Николь Мирзоева',
            'email' => 'nicol@example.com',
            'phone' => '+7 (999) 123-45-67',
            'password' => Hash::make('password'),
            'user_type' => 'master',
            'address' => 'ул. Красная, 15',
            'is_active' => true,
        ]);
        
        // Создаем профиль мастера
        Master::create([
            'name' => $masterUser->name,
            'email' => $masterUser->email,
            'phone' => $masterUser->phone,
            'user_id' => $masterUser->id,
            'specialization' => 'Маникюр, педикюр, наращивание',
            'experience_years' => 5,
            'bio' => 'Профессиональный мастер маникюра с 5-летним опытом работы. Специализируюсь на дизайне ногтей и наращивании.',
            'is_active' => true,
        ]);

        // Создаем тестовый салон
        User::create([
            'name' => 'Администратор салона',
            'email' => 'salon@example.com',
            'phone' => '+7 (999) 987-65-43',
            'password' => Hash::make('password'),
            'user_type' => 'salon',
            'address' => 'ул. Ленина, д. 15',
            'is_active' => true,
        ]);
    }
} 