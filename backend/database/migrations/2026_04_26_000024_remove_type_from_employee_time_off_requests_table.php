<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_time_off_requests', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('employee_time_off_requests', function (Blueprint $table) {
            $table->enum('type', ['vacation', 'sickness', 'personal'])->after('date');
        });
    }
};
