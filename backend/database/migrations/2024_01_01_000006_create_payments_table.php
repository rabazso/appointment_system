<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->integer('amount');
            $table->string('method',20)->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('payments'); }
};
