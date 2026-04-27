<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopImageRequest;
use App\Http\Resources\ShopImageResource;
use App\Models\ShopImage;
use App\Services\ImagePreviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ShopImageController extends Controller
{
    public function index()
    {
        return ShopImageResource::collection(ShopImage::all());
    }

    public function store(
        StoreShopImageRequest $request,
        ImagePreviewService $imagePreviewService
    ): ShopImageResource
    {
        $file = $request->file('image');
        $preview = $imagePreviewService->createFromFile($file);

        $shopImage = ShopImage::create([
            'type' => $file->getMimeType(),
            'original' => $file->get(),
            'preview' => $preview,
        ]);

        return new ShopImageResource($shopImage);
    }

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

    public function showOriginal(ShopImage $shopImage): Response
    {
        return response($shopImage->original, headers: [
            'Content-Type' => $shopImage->type,
        ]);
    }

    public function showPreview(ShopImage $shopImage): Response
    {
        return response($shopImage->preview, headers: [
            'Content-Type' => $shopImage->type,
        ]);
    }

    public function destroy(ShopImage $shopImage): JsonResponse
    {
        $shopImage->delete();

        return response()->json([
            'message' => 'Shop image deleted successfully',
        ]);
    }
}
