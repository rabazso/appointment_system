<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE employee_images ADD original LONGBLOB');
        DB::statement('ALTER TABLE employee_images ADD preview LONGBLOB');
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_images');
    }
};
