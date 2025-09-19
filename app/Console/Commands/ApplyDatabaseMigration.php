<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplyDatabaseMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:apply-database-migration {--fresh : Сбросить базу данных перед миграцией} {--seed : Заполнить базу тестовыми данными после миграции}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Применяет все миграции для обновления структуры базы данных и, опционально, заполняет ее тестовыми данными';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Запрос подтверждения перед выполнением
        if (!$this->confirm('Вы уверены, что хотите выполнить миграцию базы данных? Это может привести к потере данных.')) {
            $this->info('Операция отменена.');
            return;
        }

        $this->info('Начинаем процесс миграции базы данных...');

        // Создаем резервную копию базы данных
        $this->info('Создание резервной копии базы данных...');
        $this->makeBackup();

        // Если указан флаг --fresh, сбрасываем базу данных
        if ($this->option('fresh')) {
            $this->info('Сбрасываем базу данных...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('База данных сброшена успешно.');
        } else {
            // Иначе просто применяем миграции
            $this->info('Применяем миграции...');
            Artisan::call('migrate', ['--force' => true]);
            $this->info('Миграции применены успешно.');
        }

        // Запускаем скрипт миграции данных
        $this->info('Переносим данные из старой структуры в новую...');
        try {
            Artisan::call('app:migrate-data-structure', [
                '--fresh' => $this->option('fresh')
            ]);
            $this->info('Перенос данных выполнен успешно.');
        } catch (\Exception $e) {
            $this->error('Ошибка при переносе данных: ' . $e->getMessage());
            $this->info('Продолжаем процесс миграции...');
        }

        // Если указан флаг --seed, заполняем базу тестовыми данными
        if ($this->option('seed')) {
            $this->info('Заполняем базу тестовыми данными...');
            Artisan::call('db:seed', ['--class' => 'Database\Seeders\TestDatabaseSeeder', '--force' => true]);
            $this->info('База данных заполнена тестовыми данными.');
        }

        // Очистка кэша и оптимизация
        $this->info('Очищаем кэш и оптимизируем приложение...');
        Artisan::call('optimize:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        $this->info('Очистка кэша и оптимизация завершены.');

        $this->info('Миграция базы данных завершена успешно.');
    }

    /**
     * Создает резервную копию базы данных перед миграцией
     */
    private function makeBackup()
    {
        try {
            // Получаем имя текущей базы данных
            $database = DB::connection()->getDatabaseName();
            
            // Создаем имя для резервной копии с текущей датой и временем
            $backupName = $database . '_backup_' . date('Y_m_d_His');
            
            // Проверяем, используется ли MySQL или SQLite
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'mysql') {
                // Для MySQL можно использовать команду CREATE DATABASE ... LIKE
                DB::statement("CREATE DATABASE IF NOT EXISTS {$backupName} LIKE {$database}");
                
                // Получаем список таблиц
                $tables = DB::select('SHOW TABLES');
                $tableColumn = "Tables_in_{$database}";
                
                // Копируем данные из каждой таблицы
                foreach ($tables as $table) {
                    $tableName = $table->$tableColumn;
                    DB::statement("INSERT INTO {$backupName}.{$tableName} SELECT * FROM {$database}.{$tableName}");
                }
                
                $this->info("Резервная копия создана в базе данных {$backupName}");
            } elseif ($driver === 'sqlite') {
                // Для SQLite можно просто скопировать файл базы данных
                $databasePath = DB::connection()->getConfig('database');
                $backupPath = $databasePath . '.backup';
                copy($databasePath, $backupPath);
                $this->info("Резервная копия создана в файле {$backupPath}");
            } else {
                $this->warn("Автоматическое создание резервной копии не поддерживается для драйвера {$driver}. Пожалуйста, создайте резервную копию вручную.");
            }
        } catch (\Exception $e) {
            $this->warn("Не удалось создать резервную копию: " . $e->getMessage());
            if (!$this->confirm('Продолжить без резервной копии?')) {
                throw new \Exception('Операция отменена пользователем.');
            }
        }
    }
}
