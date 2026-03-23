<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeImage;
use Illuminate\Database\Seeder;

class EmployeeImageSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'Blowout Ben' => 'blowout_ben.png',
            'Crispy Chris' => 'crispy_chris.png',
            'Bouncy Bella' => 'bouncy_bella.png',
            'Loud Lucy' => 'loud_lucy.png',
            'Haircut Harry' => 'haircut_harry.png',
        ];

        foreach ($definitions as $employeeName => $fileName) {
            $employee = Employee::where('name', $employeeName)->first();

            $imagePath = '/storage/images/employees/' . $fileName;

            $employee->update([
                'photo_path' => $imagePath,
            ]);

            EmployeeImage::create([
                'employee_id' => $employee->id,
                'image_path' => $imagePath,
            ]);
        }
    }
}
