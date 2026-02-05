<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('default_duration');
            $table->integer('default_price');
            $table->boolean('active')->default(true);
            $table->timestamps();
});

    }
    public function down() {
        Schema::dropIfExists('services'); 
    }
};