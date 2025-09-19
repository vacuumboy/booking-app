<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateClientsAndServicesToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-clients-and-services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Привязывает существующих клиентов и услуги к пользователям';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем миграцию клиентов и услуг к пользователям...');
        
        // Получаем всех пользователей
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->error('Нет пользователей в системе!');
            return 1;
        }
        
        // Начинаем транзакцию
        DB::beginTransaction();
        
        try {
            // Обрабатываем клиентов без привязки к пользователю
            $clientsCount = $this->migrateClients($users);
            
            // Обрабатываем услуги без привязки к пользователю
            $servicesCount = $this->migrateServices($users);
            
            // Фиксируем изменения
            DB::commit();
            
            $this->info("Миграция успешно завершена!");
            $this->info("Обработано клиентов: {$clientsCount}");
            $this->info("Обработано услуг: {$servicesCount}");
            
            return 0;
        } catch (\Exception $e) {
            // Откатываем изменения в случае ошибки
            DB::rollBack();
            
            $this->error("Произошла ошибка: {$e->getMessage()}");
            return 1;
        }
    }
    
    /**
     * Привязываем клиентов к пользователям.
     */
    private function migrateClients($users)
    {
        $this->info('Обрабатываем клиентов...');
        
        // Получаем клиентов без привязки к пользователю
        $clients = Client::whereNull('user_id')->get();
        
        if ($clients->isEmpty()) {
            $this->info('Нет клиентов для обработки.');
            return 0;
        }
        
        $count = 0;
        
        // Спрашиваем пользователя, какому пользователю привязать клиентов
        $this->info('Выберите пользователя, к которому нужно привязать клиентов:');
        
        foreach ($users as $index => $user) {
            $this->line("[{$index}] {$user->name} ({$user->email})");
        }
        
        $userIndex = $this->ask('Введите номер пользователя:');
        
        if (!isset($users[$userIndex])) {
            $this->error('Некорректный номер пользователя!');
            return 0;
        }
        
        $selectedUser = $users[$userIndex];
        
        // Привязываем клиентов к выбранному пользователю
        foreach ($clients as $client) {
            $client->user_id = $selectedUser->id;
            $client->save();
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Привязываем услуги к пользователям.
     */
    private function migrateServices($users)
    {
        $this->info('Обрабатываем услуги...');
        
        // Получаем услуги без привязки к пользователю
        $services = Service::whereNull('user_id')->get();
        
        if ($services->isEmpty()) {
            $this->info('Нет услуг для обработки.');
            return 0;
        }
        
        $count = 0;
        
        // Спрашиваем пользователя, какому пользователю привязать услуги
        $this->info('Выберите пользователя, к которому нужно привязать услуги:');
        
        foreach ($users as $index => $user) {
            $this->line("[{$index}] {$user->name} ({$user->email})");
        }
        
        $userIndex = $this->ask('Введите номер пользователя:');
        
        if (!isset($users[$userIndex])) {
            $this->error('Некорректный номер пользователя!');
            return 0;
        }
        
        $selectedUser = $users[$userIndex];
        
        // Привязываем услуги к выбранному пользователю
        foreach ($services as $service) {
            $service->user_id = $selectedUser->id;
            $service->save();
            $count++;
        }
        
        return $count;
    }
}
