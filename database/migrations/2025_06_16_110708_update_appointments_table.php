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
        Schema::table('appointments', function (Blueprint $table) {
            // Добавление нового поля для хранения пользователя, создавшего запись
            $table->unsignedBigInteger('created_by_user_id')->nullable()->after('notes');
            
            // Добавляем поля для удобства анализа данных
            $table->boolean('is_confirmed')->default(false)->after('status');
            $table->boolean('is_paid')->default(false)->after('is_confirmed');
            $table->string('payment_method')->nullable()->after('is_paid');
            
            // Добавляем внешние ключи
            $table->foreign('client_id')->references('id')->on('clients')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('master_id')->references('id')->on('masters')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('service_id')->references('id')->on('services')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')
                  ->onDelete('set null')->onUpdate('cascade');
            
            // Добавляем индексы для оптимизации запросов
            $table->index(['start_time', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Удаляем добавленные поля
            $table->dropColumn([
                'created_by_user_id',
                'is_confirmed',
                'is_paid',
                'payment_method'
            ]);
            
            // Удаляем внешние ключи
            $table->dropForeign(['client_id']);
            $table->dropForeign(['master_id']);
            $table->dropForeign(['service_id']);
            $table->dropForeign(['created_by_user_id']);
            
            // Удаляем индексы
            $table->dropIndex(['start_time', 'status']);
        });
    }
};
