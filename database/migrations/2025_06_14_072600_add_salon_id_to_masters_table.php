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
            $table->foreignId('salon_id')->nullable()->constrained('users')->onDelete('set null');
            $table->index('salon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->dropForeign(['salon_id']);
            $table->dropIndex(['salon_id']);
            $table->dropColumn('salon_id');
        });
    }
};
