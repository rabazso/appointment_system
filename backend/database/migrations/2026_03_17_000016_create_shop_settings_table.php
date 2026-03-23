<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name')->default('My Shop');
            $table->string('shop_logo')->nullable();
            $table->integer('max_advance_booking_days');
            $table->integer('cancellation_deadline_hours');
            $table->integer('slot_interval_minutes');
            $table->boolean('sync_opening_hours_with_employee_schedule')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};
