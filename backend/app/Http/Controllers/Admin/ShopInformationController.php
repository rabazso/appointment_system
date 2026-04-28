<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShopInformationRequest;
use App\Http\Resources\Shop\ShopInformationResource;
use App\Models\ShopInformation;

class ShopInformationController extends Controller
{
    public function update(UpdateShopInformationRequest $request, ShopInformation $shopInformation): ShopInformationResource
    {
        $shopInformation->update($request->validated());

        return new ShopInformationResource($shopInformation);
    }
}
