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
            $table->text('hospital_decline_reason')->nullable();
            $table->text('responder_notes')->nullable();
            $table->timestamp('admission_fee_paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergencies', function (Blueprint $table) {
            $table->dropColumn(['hospital_decline_reason', 'responder_notes', 'admission_fee_paid_at']);
        });
    }
};
