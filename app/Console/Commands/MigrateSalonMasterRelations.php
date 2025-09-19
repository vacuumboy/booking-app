<?php

namespace App\Console\Commands;

use App\Models\Master;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSalonMasterRelations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-salon-master-relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Мигрирует связи мастеров и салонов из старой структуры (salon_id) в новую (salon_master)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем миграцию связей мастеров и салонов...');
        
        // Получаем всех мастеров с заполненным salon_id
        $masters = Master::whereNotNull('salon_id')->get();
        
        if ($masters->isEmpty()) {
            $this->info('Нет мастеров с заполненным salon_id для миграции');
            return Command::SUCCESS;
        }
        
        $this->info("Найдено {$masters->count()} мастеров для миграции");
        
        // Начинаем транзакцию
        DB::beginTransaction();
        
        try {
            $migratedCount = 0;
            $errorsCount = 0;
            
            foreach ($masters as $master) {
                // Проверяем существование салона
                $salon = User::where('id', $master->salon_id)
                    ->where('user_type', 'salon')
                    ->first();
                    
                if (!$salon) {
                    $this->warn("Не найден салон с ID {$master->salon_id} для мастера #{$master->id} ({$master->name})");
                    $errorsCount++;
                    continue;
                }
                
                // Проверяем, не существует ли уже связь
                $existingRelation = DB::table('salon_master')
                    ->where('salon_id', $salon->id)
                    ->where('master_id', $master->id)
                    ->exists();
                    
                if ($existingRelation) {
                    $this->line("Связь между салоном #{$salon->id} и мастером #{$master->id} уже существует");
                    continue;
                }
                
                // Создаем новую связь
                DB::table('salon_master')->insert([
                    'salon_id' => $salon->id,
                    'master_id' => $master->id,
                    'is_active' => $master->is_active,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info("Создана связь между салоном #{$salon->id} ({$salon->name}) и мастером #{$master->id} ({$master->name})");
                $migratedCount++;
            }
            
            DB::commit();
            
            $this->info("Миграция завершена: создано {$migratedCount} новых связей, ошибок: {$errorsCount}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Произошла ошибка: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
