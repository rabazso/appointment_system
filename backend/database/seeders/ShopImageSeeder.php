<?php

namespace Database\Seeders;

use App\Models\ShopImage;
use App\Services\ImagePreviewService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ShopImageSeeder extends Seeder
{
    public function run(ImagePreviewService $imagePreviewService): void
    {
        $fileNames = [
            'blowout_ben.png',
            'crispy_chris.png',
            'bouncy_bella.png',
            'loud_lucy.png',
            'haircut_harry.png',
        ];

        foreach ($fileNames as $fileName) {
            $absolutePath = storage_path('app/public/images/employees/' . $fileName);

            ShopImage::create([
                'type' => File::mimeType($absolutePath),
                'original' => File::get($absolutePath),
                'preview' => $imagePreviewService->createFromPath($absolutePath),
            ]);
        }
    }
}
