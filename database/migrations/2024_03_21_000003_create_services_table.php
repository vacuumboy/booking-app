<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration')->comment('Duration in minutes');
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Создаем связующую таблицу между мастерами и услугами
        Schema::create('master_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2)->nullable(); // Индивидуальная цена мастера
            $table->timestamps();

            $table->unique(['user_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_services');
        Schema::dropIfExists('services');
    }
}; 