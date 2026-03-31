<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeImage;
use App\Services\ImagePreviewService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class EmployeeImageSeeder extends Seeder
{
    public function run(ImagePreviewService $imagePreviewService): void
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
            $absolutePath = storage_path('app/public/images/employees/' . $fileName);

            $image = File::get($absolutePath);
            $preview = $imagePreviewService->createFromPath($absolutePath);

            $employeeImage = EmployeeImage::create([
                'employee_id' => $employee->id,
                'type' => File::mimeType($absolutePath),
                'original' => $image,
                'preview' => $preview,
            ]);

            $employee->update([
                'profile_image_id' => $employeeImage->id,
            ]);
        }
    }
}
