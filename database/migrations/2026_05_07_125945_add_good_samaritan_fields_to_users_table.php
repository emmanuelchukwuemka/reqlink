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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_good_samaritan')->default(false);
            $table->string('samaritan_profession')->nullable(); // e.g., Doctor, Nurse, EMT
            $table->boolean('samaritan_active')->default(false);
            $table->decimal('last_known_lat', 10, 8)->nullable();
            $table->decimal('last_known_lng', 11, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_good_samaritan', 'samaritan_profession', 'samaritan_active', 'last_known_lat', 'last_known_lng']);
        });
    }
};
