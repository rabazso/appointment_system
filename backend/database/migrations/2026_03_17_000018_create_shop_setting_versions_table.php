<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_setting_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_setting_id')->constrained('shop_settings')->cascadeOnDelete();
            $table->integer('default_booking_interval_minutes');
            $table->integer('default_booking_window_days');
            $table->integer('cancellation_deadline_hours');
            $table->dateTime('valid_from');
            $table->dateTime('valid_to')->nullable();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('shop_setting_versions');
    }
};
