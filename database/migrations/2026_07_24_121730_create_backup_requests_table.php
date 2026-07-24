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
        Schema::create('backup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('responder_id')->constrained('responders')->cascadeOnDelete();
            $table->foreignId('emergency_id')->nullable()->constrained('emergencies')->nullOnDelete();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('message')->nullable();
            $table->enum('status', ['pending', 'acknowledged', 'resolved'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_requests');
    }
};
