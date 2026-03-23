<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_service_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_service_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('duration');
            $table->integer('price');
            $table->dateTime('valid_from');
            $table->dateTime('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_service_versions');
    }
};
