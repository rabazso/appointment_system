<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $backendBase = rtrim((string) config('app.url'), '/');
        if ($backendBase !== '' && !preg_match('#^https?://#', $backendBase)) {
            $backendBase = 'http://' . $backendBase;
        }

        $employees = DB::table('employees')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->select('employees.id as employee_id', 'users.name as user_name')
            ->get();

        foreach ($employees as $employee) {
            $photoUrl = $backendBase . '/storage/images/' . $employee->employee_id . '/' . $employee->user_name . '.png';

            DB::table('employees')
                ->where('id', $employee->employee_id)
                ->update(['photo_url' => $photoUrl]);

            $exists = DB::table('employee_gallery')
                ->where('employee_id', $employee->employee_id)
                ->where('image_url', $photoUrl)
                ->exists();

            if (!$exists) {
                DB::table('employee_gallery')->insert([
                    'employee_id' => $employee->employee_id,
                    'image_url' => $photoUrl,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('users')
            ->whereIn('id', function ($query) {
                $query->select('user_id')->from('employees');
            })
            ->whereNull('role')
            ->update(['role' => 'employee']);
    }

    public function down(): void
    {
        DB::table('employees')->update(['photo_url' => null]);
    }
};
