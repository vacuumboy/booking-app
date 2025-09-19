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
        Schema::create('salon_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('master_id')->constrained('masters')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Создаем уникальный индекс для пары salon_id и master_id
            $table->unique(['salon_id', 'master_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salon_master');
    }
};
