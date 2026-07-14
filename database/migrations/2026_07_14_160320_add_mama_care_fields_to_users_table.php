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
            $table->boolean('mama_care_active')->default(false)->after('samaritan_active');
            $table->date('pregnancy_due_date')->nullable()->after('mama_care_active');
            $table->boolean('pregnancy_high_risk')->default(false)->after('pregnancy_due_date');
            $table->string('preferred_maternity_hospital')->nullable()->after('pregnancy_high_risk');
            $table->string('obgyn_contact')->nullable()->after('preferred_maternity_hospital');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mama_care_active',
                'pregnancy_due_date',
                'pregnancy_high_risk',
                'preferred_maternity_hospital',
                'obgyn_contact'
            ]);
        });
    }
};
