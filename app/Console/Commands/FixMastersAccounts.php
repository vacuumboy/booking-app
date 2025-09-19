<?php

namespace App\Console\Commands;

use App\Models\Master;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FixMastersAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-masters-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает аккаунты пользователей для существующих мастеров и связывает их';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем исправление связей мастеров с аккаунтами...');

        $masters = Master::whereNull('user_id')->get();
        
        if ($masters->isEmpty()) {
            $this->info('Нет мастеров без связи с пользователями');
            return Command::SUCCESS;
        }
        
        $this->info("Найдено {$masters->count()} мастеров без связи с пользователями");
        
        $createdCount = 0;
        $linkedCount = 0;
        $errors = 0;
        
        // Начинаем транзакцию
        DB::beginTransaction();
        
        try {
            foreach ($masters as $master) {
                // Сначала проверяем, существует ли пользователь с таким email
                $user = User::where('email', $master->email)->first();
                    
                if ($user) {
                    // Если пользователь существует, проверяем тип и связываем
                    if ($user->user_type !== 'master') {
                        $this->warn("Пользователь с email {$master->email} имеет неправильный тип: {$user->user_type}. Изменяем на 'master'");
                        $user->user_type = 'master';
                        $user->save();
                    }
                    
                    $master->user_id = $user->id;
                    $master->save();
                    
                    $this->line("Связан мастер #{$master->id} ({$master->name}) с существующим пользователем #{$user->id}");
                    $linkedCount++;
                } else {
                    // Если пользователя нет, создаем нового
                    $phone = $master->phone ?? '00000000';
                    $address = 'Адрес не указан';
                    $temporaryPassword = 'password123'; // Временный пароль, который позже можно сбросить
                    
                    $user = User::create([
                        'name' => $master->name,
                        'email' => $master->email,
                        'phone' => $phone,
                        'address' => $address,
                        'password' => Hash::make($temporaryPassword),
                        'user_type' => 'master',
                        'is_active' => true,
                    ]);
                    
                    $master->user_id = $user->id;
                    $master->save();
                    
                    $this->info("Создан новый пользователь #{$user->id} для мастера #{$master->id} ({$master->name})");
                    $createdCount++;
                }
            }
            
            DB::commit();
            
            $this->info("Операция завершена: создано {$createdCount} новых пользователей, связано {$linkedCount} мастеров, ошибок: {$errors}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Произошла ошибка: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
