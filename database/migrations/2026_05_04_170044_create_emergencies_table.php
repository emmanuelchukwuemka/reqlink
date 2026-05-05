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
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('emergency_type_id')->constrained();
            $table->string('subtype')->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('address')->nullable();
            $table->enum('status', ['pending', 'dispatched', 'enroute', 'arrived', 'resolved', 'cancelled'])->default('pending');
            $table->integer('priority')->default(1); // 1-5
            $table->integer('severity_score')->default(0);
            $table->foreignId('assigned_responder_id')->nullable();
            $table->integer('eta_minutes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergencies');
    }
};
