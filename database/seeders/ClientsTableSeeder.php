<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех пользователей для возможной связи клиентов с ними
        $users = User::all();
        
        // Получаем отдельно пользователей типа "салон" и "мастер" для создания клиентов
        $salonUsers = $users->where('user_type', 'salon');
        $masterUsers = $users->where('user_type', 'master');
        $clientUsers = $users->where('user_type', 'client');
        
        // 1. Создаем клиентов для пользователей типа "клиент"
        foreach ($clientUsers as $user) {
            Client::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'notes' => 'Клиент с учетной записью',
                'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'address' => fake()->address,
                'preferred_communication' => collect(['phone', 'email', 'sms'])->random(),
            ]);
        }
        
        // 2. Создаем клиентов для салонов
        foreach ($salonUsers as $salon) {
            // Создаем несколько клиентов для каждого салона
            for ($i = 0; $i < rand(3, 7); $i++) {
                Client::create([
                    'user_id' => $salon->id,
                    'name' => fake()->name,
                    'phone' => fake()->phoneNumber,
                    'email' => fake()->unique()->safeEmail,
                    'notes' => fake()->sentence,
                    'birth_date' => fake()->optional(0.7)->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                    'address' => fake()->optional(0.5)->address,
                    'preferred_communication' => collect(['phone', 'email', 'sms', null])->random(),
                ]);
            }
        }
        
        // 3. Создаем клиентов для мастеров
        foreach ($masterUsers as $master) {
            // Создаем несколько клиентов для каждого мастера
            for ($i = 0; $i < rand(2, 5); $i++) {
                Client::create([
                    'user_id' => $master->id,
                    'name' => fake()->name,
                    'phone' => fake()->phoneNumber,
                    'email' => fake()->unique()->safeEmail,
                    'notes' => fake()->optional(0.7)->sentence,
                    'birth_date' => fake()->optional(0.6)->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                    'preferred_communication' => collect(['phone', 'email', 'sms', null])->random(),
                ]);
            }
        }
        
        // 4. Создаем несколько общих клиентов с помощью фабрики
        Client::factory()
            ->count(10)
            ->state(function () use ($users) {
                return [
                    'user_id' => $users->random()->id,
                ];
            })
            ->create();
    }
}
