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
        Schema::create('hospital_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->string('patient_name');
            $table->enum('bed_type', ['general', 'icu'])->default('general');
            $table->timestamp('expected_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['reserved', 'admitted', 'cancelled'])->default('reserved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_reservations');
    }
};
