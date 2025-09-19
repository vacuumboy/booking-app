<?php

namespace App\Console\Commands;

use App\Models\Master;
use App\Models\User;
use Illuminate\Console\Command;

class TestSalonMasterRelation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-salon-master-relation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирует связь между салоном и мастерами';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Тестирование связи между салоном и мастерами...');
        
        // Получаем салон
        $salon = User::where('user_type', 'salon')->first();
        
        if (!$salon) {
            $this->error('Не найден пользователь типа "salon"');
            return Command::FAILURE;
        }
        
        $this->info("Найден салон: {$salon->name} (ID: {$salon->id})");
        
        // Получаем мастера
        $master = Master::first();
        
        if (!$master) {
            $this->error('Не найден ни один мастер');
            return Command::FAILURE;
        }
        
        $this->info("Найден мастер: {$master->name} (ID: {$master->id})");
        
        // Проверяем, есть ли уже связь
        $existingRelation = $salon->masters()->where('masters.id', $master->id)->exists();
        
        if ($existingRelation) {
            $this->info("Мастер {$master->name} уже связан с салоном {$salon->name}");
        } else {
            // Создаем связь
            $salon->masters()->attach($master->id, ['is_active' => true]);
            $this->info("Создана связь между салоном {$salon->name} и мастером {$master->name}");
        }
        
        // Создаем нового мастера
        $newMaster = Master::create([
            'name' => 'Новый Мастер',
            'email' => 'new.master@example.com',
            'phone' => '+7 (999) 111-22-33',
            'is_active' => true,
        ]);
        
        $this->info("Создан новый мастер: {$newMaster->name} (ID: {$newMaster->id})");
        
        // Связываем нового мастера с салоном
        $salon->masters()->attach($newMaster->id, ['is_active' => true]);
        $this->info("Создана связь между салоном {$salon->name} и новым мастером {$newMaster->name}");
        
        // Получаем всех мастеров салона
        $masters = $salon->masters()->get();
        
        $this->info("Мастера салона {$salon->name}:");
        foreach ($masters as $salonMaster) {
            $this->line(" - {$salonMaster->name} (ID: {$salonMaster->id})");
        }
        
        return Command::SUCCESS;
    }
}
