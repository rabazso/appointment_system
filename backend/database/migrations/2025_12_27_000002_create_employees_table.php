<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration {
    public function up() {
        Schema::create('employees', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(User::class);
    $table->text('bio')->nullable();
    $table->string('photo_url')->nullable();
});

    }
    public function down() {
        Schema::dropIfExists('employees'); 
    }
};