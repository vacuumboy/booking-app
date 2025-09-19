<?php

namespace App\Console\Commands;

use App\Models\Master;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkMastersToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:link-masters-to-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Связывает существующих мастеров с пользователями по email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем связывание мастеров с пользователями...');

        $masters = Master::whereNull('user_id')->get();
        
        if ($masters->isEmpty()) {
            $this->info('Нет мастеров без связи с пользователями');
            return Command::SUCCESS;
        }
        
        $this->info("Найдено {$masters->count()} мастеров без связи с пользователями");
        
        $linkedCount = 0;
        $errors = 0;
        
        // Начинаем транзакцию
        DB::beginTransaction();
        
        try {
            foreach ($masters as $master) {
                // Ищем пользователя с тем же email
                $user = User::where('email', $master->email)
                    ->where('user_type', 'master')
                    ->first();
                    
                if ($user) {
                    $this->line("Связываем мастера #{$master->id} ({$master->name}) с пользователем #{$user->id}");
                    
                    $master->user_id = $user->id;
                    $master->save();
                    
                    $linkedCount++;
                } else {
                    $this->warn("Не найден пользователь с email {$master->email} и типом 'master'");
                    $errors++;
                }
            }
            
            DB::commit();
            
            $this->info("Связывание завершено: связано {$linkedCount} мастеров, ошибок: {$errors}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Произошла ошибка: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
