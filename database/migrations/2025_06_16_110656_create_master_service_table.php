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
        Schema::create('master_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_id');
            $table->unsignedBigInteger('service_id');
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->integer('custom_duration')->nullable();
            $table->timestamps();
            
            // Внешние ключи
            $table->foreign('master_id')->references('id')->on('masters')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('service_id')->references('id')->on('services')
                  ->onDelete('cascade')->onUpdate('cascade');
                  
            // Уникальное сочетание мастера и услуги
            $table->unique(['master_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_service');
    }
};
