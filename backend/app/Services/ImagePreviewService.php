<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImagePreviewService
{
    public function createFromFile($file, int $width = 300, int $height = 300): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->get());
        $image->scaleDown(width: $width, height: $height);

        return (string) $image->encode();
    }

    public function createFromPath(string $path, int $width = 300, int $height = 300): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($path);
        $image->scaleDown(width: $width, height: $height);

        return (string) $image->encode();
    }
}
