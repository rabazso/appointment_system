<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_booking_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_rule_configuration_id')->constrained('employee_booking_rule_configurations')->cascadeOnDelete();
            $table->integer('booking_interval_minutes');
            $table->integer('booking_window_days');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_booking_rules');
    }
};
