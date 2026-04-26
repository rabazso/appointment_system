<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopInformationRequest;
use App\Http\Requests\UpdateShopInformationRequest;
use App\Http\Resources\ShopInformationResource;
use App\Models\ShopInformation;
use Illuminate\Http\JsonResponse;

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

    public function store(StoreShopInformationRequest $request): ShopInformationResource
    {
        $shopInformation = ShopInformation::create($request->validated());

        return new ShopInformationResource($shopInformation);
    }

    public function update(UpdateShopInformationRequest $request, ShopInformation $shopInformation): ShopInformationResource
    {
        $shopInformation->update($request->validated());

        return new ShopInformationResource($shopInformation);
    }

    public function destroy(ShopInformation $shopInformation): JsonResponse
    {
        $shopInformation->delete();

        return response()->json(['message' => 'Shop information deleted successfully']);
    }
}
