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
        Schema::table('reminder_templates', function (Blueprint $table) {
            $table->string('language', 2)->default('ru')->after('name');
            $table->index(['user_id', 'language', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminder_templates', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'language', 'is_active']);
            $table->dropColumn('language');
        });
    }
}; 