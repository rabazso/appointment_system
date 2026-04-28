<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\ShopImageResource;
use App\Models\ShopImage;
use Illuminate\Http\Response;

class ShopImageController extends Controller
{
    public function index()
    {
        return ShopImageResource::collection(ShopImage::all());
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
}
