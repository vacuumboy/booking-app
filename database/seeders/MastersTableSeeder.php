<?php

namespace Database\Seeders;

use App\Models\Master;
use App\Models\User;
use Illuminate\Database\Seeder;

class MastersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех пользователей типа "мастер"
        $masterUsers = User::where('user_type', 'master')->get();
        
        // Получаем салон для связи мастеров с ним
        $salon = User::where('user_type', 'salon')
                    ->where('email', 'salon@example.com')
                    ->first();
        
        // Создаем записи мастеров для каждого пользователя типа "мастер"
        foreach ($masterUsers as $user) {
            $specializations = ['Маникюр', 'Педикюр', 'Стрижка', 'Окрашивание', 'Ламинирование'];
            $randomSpecialization = $specializations[array_rand($specializations)];
            
            $master = Master::create([
                'user_id' => $user->id,
                'salon_id' => $salon ? $salon->id : null,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'bio' => $user->bio ?: 'Опытный мастер',
                'photo_path' => $user->photo_path,
                'is_active' => $user->is_active,
                'specialization' => $randomSpecialization,
                'experience_years' => rand(1, 10),
                'certificates' => ['certificate_' . rand(1000, 9999) . '.jpg'],
                'rating' => rand(35, 50) / 10, // Рейтинг от 3.5 до 5.0
            ]);
        }
        
        // Создаем несколько независимых мастеров (не связанных с пользователями)
        // для демонстрации разных вариантов использования
        $specializations = ['Визажист', 'Массажист', 'Косметолог', 'Стилист', 'Визажист-стилист'];
        
        for ($i = 0; $i < 3; $i++) {
            $randomSpecialization = $specializations[array_rand($specializations)];
            $salonUser = User::where('user_type', 'salon')
                           ->inRandomOrder()
                           ->first();
            
            Master::create([
                'user_id' => null,
                'salon_id' => $salonUser ? $salonUser->id : null,
                'name' => 'Мастер ' . ($i + 1),
                'email' => 'independent.master' . ($i + 1) . '@example.com',
                'phone' => '+7 999 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'bio' => 'Независимый мастер ' . $randomSpecialization,
                'is_active' => true,
                'specialization' => $randomSpecialization,
                'experience_years' => rand(1, 15),
                'certificates' => ['certificate_' . rand(1000, 9999) . '.jpg', 'certificate_' . rand(1000, 9999) . '.jpg'],
                'rating' => rand(35, 50) / 10, // Рейтинг от 3.5 до 5.0
            ]);
        }
    }
}
