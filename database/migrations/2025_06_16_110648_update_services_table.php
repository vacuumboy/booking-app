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
        Schema::table('services', function (Blueprint $table) {
            // Добавляем новые поля
            $table->string('color_code', 7)->nullable()->after('category');
            $table->text('requirements')->nullable()->after('description');
            
            // Модифицируем существующие поля
            $table->renameColumn('category', 'category_name');
            
            // Добавляем внешние ключи
            $table->foreign('user_id')->references('id')->on('users')
                  ->onDelete('cascade')->onUpdate('cascade');
                  
            // Добавляем индексы для оптимизации запросов
            $table->index(['category_name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Удаляем добавленные поля
            $table->dropColumn(['color_code', 'requirements']);
            
            // Возвращаем прежние названия полей
            $table->renameColumn('category_name', 'category');
            
            // Удаляем внешние ключи
            $table->dropForeign(['user_id']);
            
            // Удаляем индексы
            $table->dropIndex(['category_name', 'is_active']);
        });
    }
};
