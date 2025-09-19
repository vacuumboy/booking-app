<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('name_ru')->after('name')->nullable();
            $table->string('name_lv')->after('name_ru')->nullable();
            $table->string('name_en')->after('name_lv')->nullable();
            
            $table->index(['user_id', 'is_active']);
        });
        
        // Копируем существующие названия в поле name_ru
        DB::statement('UPDATE services SET name_ru = name WHERE name_ru IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['name_ru', 'name_lv', 'name_en']);
        });
    }
}; 