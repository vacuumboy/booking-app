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
            // Удаляем уникальное ограничение с поля email
            $table->dropUnique(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            // Восстанавливаем уникальное ограничение для поля email
            $table->unique('email');
        });
    }
};
