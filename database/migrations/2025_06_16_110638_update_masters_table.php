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
        Schema::table('masters', function (Blueprint $table) {
            // Добавляем новые поля из таблицы users
            $table->string('specialization')->nullable()->after('bio');
            $table->integer('experience_years')->nullable()->after('specialization');
            $table->json('certificates')->nullable()->after('experience_years');
            
            // Добавляем поле для рейтинга
            $table->decimal('rating', 3, 2)->nullable()->after('certificates');
            
            // Обеспечиваем корректные внешние ключи
            $table->foreign('user_id')->references('id')->on('users')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('salon_id')->references('id')->on('users')
                  ->onDelete('cascade')->onUpdate('cascade');
            
            // Добавляем составной индекс для быстрого поиска
            $table->index(['salon_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            // Удаляем добавленные поля
            $table->dropColumn([
                'specialization', 
                'experience_years', 
                'certificates',
                'rating'
            ]);
            
            // Удаляем внешние ключи
            $table->dropForeign(['user_id']);
            $table->dropForeign(['salon_id']);
            
            // Удаляем индексы
            $table->dropIndex(['salon_id', 'is_active']);
        });
    }
};
