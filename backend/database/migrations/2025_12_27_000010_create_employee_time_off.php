<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('employee_time_off', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->date('date_from');
    $table->date('date_to');
    $table->enum('type', ['vacation', 'sickness', 'emergency']);
    $table->enum('status', ['requested', 'cancel_requested', 'approved', 'cancelled']);
    $table->timestamps();
});
    }
    public function down() {
        Schema::dropIfExists('employee_time_off'); 
    }
};