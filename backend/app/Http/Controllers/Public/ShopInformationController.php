<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\ShopInformationResource;
use App\Models\ShopInformation;

class ShopInformationController extends Controller
{
    public function show(): ShopInformationResource
    {
        $shopInformation = ShopInformation::query()->firstOrCreate([], [
            'email' => null,
            'phone' => null,
            'address' => null,
            'links' => [],
        ]);

        return new ShopInformationResource($shopInformation);
    }
}
