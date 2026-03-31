<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopImageRequest;
use App\Http\Resources\ShopImageResource;
use App\Models\ShopImage;
use App\Services\ImagePreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
