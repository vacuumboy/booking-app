<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Примечание: Эту миграцию следует применить только после того, как все данные
     * будут успешно перенесены в новую структуру и приложение будет работать
     * с новой связью многие-ко-многим.
     */
    public function up(): void
    {
        // Проверяем, есть ли составной индекс
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Для SQLite нужно использовать другой подход
            // Создаем временную таблицу без salon_id
            Schema::create('masters_temp', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email');
                $table->text('bio')->nullable();
                $table->string('photo_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('specialization')->nullable();
                $table->integer('experience_years')->nullable();
                $table->json('certificates')->nullable();
                $table->decimal('rating', 3, 2)->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Индексы
                $table->index('email');
                $table->index('is_active');
                $table->index('user_id');
            });
            
            // Копируем данные из старой таблицы в новую
            DB::statement('INSERT INTO masters_temp SELECT id, name, phone, email, bio, photo_path, is_active, user_id, specialization, experience_years, certificates, rating, created_at, updated_at, deleted_at FROM masters');
            
            // Удаляем старую таблицу
            Schema::dropIfExists('masters');
            
            // Переименовываем временную таблицу
            Schema::rename('masters_temp', 'masters');
        } else {
            // Для других СУБД (MySQL, PostgreSQL)
            Schema::table('masters', function (Blueprint $table) {
                // Сначала удаляем составной индекс, если он существует
                if (Schema::hasIndex('masters', 'masters_salon_id_is_active_index')) {
                    $table->dropIndex('masters_salon_id_is_active_index');
                }
                
                // Сначала удаляем внешний ключ
                $table->dropForeign(['salon_id']);
                // Затем удаляем индекс
                $table->dropIndex(['salon_id']);
                // И наконец удаляем само поле
                $table->dropColumn('salon_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            // Восстанавливаем поле
            $table->foreignId('salon_id')->nullable()->constrained('users')->onDelete('set null');
            // Восстанавливаем индекс
            $table->index('salon_id');
        });
    }
};
