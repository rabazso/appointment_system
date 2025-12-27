<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('appointments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained('users');
    $table->foreignId('employee_id')->constrained();
    $table->foreignId('service_id')->constrained();
    $table->dateTime('start_datetime');
    $table->dateTime('end_datetime');
    $table->timestamps();
});

    }
    public function down() {
        Schema::dropIfExists('appointments'); 
    }
};