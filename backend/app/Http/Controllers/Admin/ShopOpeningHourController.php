<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShopOpeningHourRequest;
use App\Http\Resources\Shop\ShopOpeningHourResource;
use App\Models\ShopOpeningHour;

class ShopOpeningHourController extends Controller
{
    public function update(UpdateShopOpeningHourRequest $request, ShopOpeningHour $shopOpeningHour): ShopOpeningHourResource
    {
        $shopOpeningHour->update($request->validated());

        return new ShopOpeningHourResource($shopOpeningHour);
    }
}
