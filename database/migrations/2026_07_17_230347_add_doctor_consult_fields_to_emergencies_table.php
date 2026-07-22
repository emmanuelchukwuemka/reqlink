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
        Schema::table('emergencies', function (Blueprint $table) {
            $table->timestamp('doctor_consult_requested_at')->nullable();
            $table->text('doctor_notes')->nullable();
            $table->timestamp('consult_fee_paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergencies', function (Blueprint $table) {
            $table->dropColumn(['doctor_consult_requested_at', 'doctor_notes', 'consult_fee_paid_at']);
        });
    }
};
