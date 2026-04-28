<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\ShopImageResource;
use App\Models\ShopImage;
use App\Services\ImagePreviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopImageController extends Controller
{
    public function storeMultiple(
        Request $request,
        ImagePreviewService $imagePreviewService
    ) {
        $validated = $request->validate([
            'images' => ['nullable', 'array'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'deleted_ids' => ['nullable', 'array'],
            'deleted_ids.*' => ['integer', 'exists:shop_images,id'],
        ]);

        $createdImages = DB::transaction(function () use ($validated, $imagePreviewService) {
            $createdImages = [];

            foreach ($validated['images'] ?? [] as $file) {
                $preview = $imagePreviewService->createFromFile($file);

                $createdImages[] = ShopImage::create([
                    'type' => $file->getMimeType(),
                    'original' => $file->get(),
                    'preview' => $preview,
                ]);
            }

            foreach ($validated['deleted_ids'] ?? [] as $id) {
                ShopImage::query()->findOrFail($id)->delete();
            }

            return $createdImages;
        });

        return ShopImageResource::collection(collect($createdImages));
    }
}
