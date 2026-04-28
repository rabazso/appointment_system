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

        $galleryFiles = array_values($definitions);

        foreach ($definitions as $employeeName => $fileName) {
            $employee = Employee::where('name', $employeeName)->first();
            if (!$employee) {
                continue;
            }

            $employeeImage = $this->createEmployeeImage($employee->id, $fileName, $imagePreviewService);

            $employee->update([
                'profile_image_id' => $employeeImage->id,
            ]);

            foreach ($this->galleryFilesFor($fileName, $galleryFiles) as $galleryFileName) {
                $this->createEmployeeImage($employee->id, $galleryFileName, $imagePreviewService);
            }
        }
    }

    private function galleryFilesFor(string $profileFileName, array $galleryFiles): array
    {
        return collect($galleryFiles)
            ->reject(fn (string $fileName) => $fileName === $profileFileName)
            ->take(3)
            ->values()
            ->all();
    }

    private function createEmployeeImage(int $employeeId, string $fileName, ImagePreviewService $imagePreviewService): EmployeeImage
    {
        $absolutePath = storage_path('app/public/images/employees/' . $fileName);

        return EmployeeImage::create([
            'employee_id' => $employeeId,
            'type' => File::mimeType($absolutePath),
            'original' => File::get($absolutePath),
            'preview' => $imagePreviewService->createFromPath($absolutePath),
        ]);
    }
}
