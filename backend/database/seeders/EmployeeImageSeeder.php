<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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
            $absolutePath = storage_path('app/public/images/employees/' . $fileName);

            $image = File::get($absolutePath);
            $preview = $this->createPreview($absolutePath);

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

    private function createPreview(string $path): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($path);
        $image->scaleDown(width: 300, height: 300);

        return (string) $image->encode();
    }
}
