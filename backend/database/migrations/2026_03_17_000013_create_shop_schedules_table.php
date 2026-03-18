<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('weekday');
            $table->boolean('is_open')->default(true);
            $table->time('open_time');
            $table->time('close_time');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_schedules');
    }
};
