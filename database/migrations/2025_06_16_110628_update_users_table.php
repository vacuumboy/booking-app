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
        Schema::table('users', function (Blueprint $table) {
            // Удаляем поля, которые будут перенесены в таблицу masters
            $table->dropColumn([
                'specialization',
                'experience_years',
                'certificates',
                'salon_license'
            ]);
            
            // Добавляем новые поля или изменяем существующие
            $table->boolean('is_verified')->default(false)->after('is_active');
            
            // Добавляем индексы для оптимизации запросов
            $table->index('user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Возвращаем удаленные поля
            $table->string('specialization')->nullable();
            $table->integer('experience_years')->nullable();
            $table->json('certificates')->nullable();
            $table->string('salon_license')->nullable();
            
            // Удаляем добавленные поля
            $table->dropColumn('is_verified');
            
            // Удаляем индексы
            $table->dropIndex(['user_type']);
        });
    }
};
