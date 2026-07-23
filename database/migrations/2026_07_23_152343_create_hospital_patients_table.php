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
        Schema::create('hospital_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('reason')->nullable();
            $table->enum('bed_type', ['general', 'icu'])->default('general');
            $table->enum('status', ['admitted', 'discharged'])->default('admitted');
            $table->text('notes')->nullable();
            $table->timestamp('admitted_at')->nullable();
            $table->timestamp('discharged_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_patients');
    }
};
