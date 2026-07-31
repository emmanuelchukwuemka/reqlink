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
        Schema::table('hospital_patients', function (Blueprint $table) {
            $table->boolean('bed_deducted')->default(false)->after('bed_type');
        });

        Schema::table('hospital_reservations', function (Blueprint $table) {
            $table->boolean('bed_deducted')->default(false)->after('bed_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospital_patients', function (Blueprint $table) {
            $table->dropColumn('bed_deducted');
        });

        Schema::table('hospital_reservations', function (Blueprint $table) {
            $table->dropColumn('bed_deducted');
        });
    }
};
