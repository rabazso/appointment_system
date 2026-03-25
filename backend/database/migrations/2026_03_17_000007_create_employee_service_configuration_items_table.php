<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_service_configuration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')->constrained('employee_service_configurations')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->boolean('uses_default_values')->default(true);
            $table->integer('duration')->nullable();
            $table->integer('price')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_service_configuration_items');
    }
};
