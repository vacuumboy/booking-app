<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Поле уже переименовано в master_id, добавляем только внешний ключ
            // Добавляем внешний ключ на таблицу мастеров
            $table->foreign('master_id')->references('id')->on('masters')
                  ->onDelete('cascade')->onUpdate('cascade');
            
            // Добавляем поля для более гибкого управления расписанием
            // Поле breaks уже существует, поэтому не добавляем его снова
            $table->string('color_code', 7)->nullable()->after('breaks');
            $table->boolean('is_visible_online')->default(true)->after('color_code');
            
            // Добавляем индексы для оптимизации запросов
            $table->index(['date', 'is_day_off']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Удаляем индексы
            $table->dropIndex(['date', 'is_day_off']);
            
            // Удаляем внешний ключ
            $table->dropForeign(['master_id']);
            
            // Удаляем добавленные поля
            $table->dropColumn(['color_code', 'is_visible_online']);
        });
    }
};
