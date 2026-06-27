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
        Schema::create('bed_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('emergency_id')->constrained('emergencies')->cascadeOnDelete();
            $table->foreignId('responder_id')->constrained('responders')->cascadeOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'declined', 'arrived', 'cancelled'])->default('pending');
            $table->integer('eta_minutes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_reservations');
    }
};
