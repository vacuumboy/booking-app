<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очистка базы данных перед заполнением тестовыми данными
        $this->cleanDatabase();
        
        // Запускаем сидеры в правильном порядке
        $this->call([
            UsersTableSeeder::class,
            MastersTableSeeder::class,
            ClientsTableSeeder::class,
            ServicesTableSeeder::class,
            SchedulesTableSeeder::class,
            AppointmentsTableSeeder::class,
        ]);
    }
    
    /**
     * Очистка базы данных от существующих данных.
     */
    private function cleanDatabase(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        DB::table('appointments')->truncate();
        DB::table('master_service')->truncate();
        DB::table('schedules')->truncate();
        DB::table('services')->truncate();
        DB::table('clients')->truncate();
        DB::table('masters')->truncate();
        DB::table('users')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
