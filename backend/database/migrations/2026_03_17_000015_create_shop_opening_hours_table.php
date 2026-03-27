<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_opening_hours', function (Blueprint $table) {
            $table->id();
            $table->integer('weekday');
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_opening_hours');
    }
};
