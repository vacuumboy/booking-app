<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Если в таблице уже есть столбец user_id, но он не nullable, делаем его nullable
        if (Schema::hasColumn('schedules', 'user_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        } 
        // Если столбца user_id нет, добавляем его
        else {
            Schema::table('schedules', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                
                // Добавляем внешний ключ
                $table->foreign('user_id')->references('id')->on('users')
                      ->onDelete('cascade')->onUpdate('cascade');
            });
        }
        
        // Обновляем существующие записи, связывая их с мастерами
        DB::statement('
            UPDATE schedules 
            SET user_id = (
                SELECT user_id FROM masters 
                WHERE masters.id = schedules.master_id
            )
            WHERE master_id IS NOT NULL AND user_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ничего не делаем при откате, так как это может привести к потере данных
    }
};
