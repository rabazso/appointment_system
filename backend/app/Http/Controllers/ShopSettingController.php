<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopSettingRequest;
use App\Http\Requests\UpdateShopSettingRequest;
use App\Http\Resources\ShopSettingResource;
use App\Http\Resources\ShopSettingVersionResource;
use App\Models\ShopSetting;
use Illuminate\Http\JsonResponse;

class ShopSettingController extends Controller
{
    public function show(): ShopSettingResource
    {
        return new ShopSettingResource(
            ShopSetting::query()->firstOrFail()
        );
    }

    public function store(StoreShopSettingRequest $request): ShopSettingResource
    {
        $shopSetting = ShopSetting::create($request->validated());

        return new ShopSettingResource($shopSetting);
    }

    public function update(UpdateShopSettingRequest $request, ShopSetting $shopSetting): ShopSettingResource
    {
        $shopSetting->update($request->validated());

        return new ShopSettingResource($shopSetting);
    }

    public function destroy(ShopSetting $shopSetting): JsonResponse
    {
        $shopSetting->delete();

        return response()->json(['message' => 'Shop setting deleted successfully']);
    }

    public function showWithValidVersion(): JsonResponse
    {
        $shopSetting = ShopSetting::query()->first();

        return response()->json([
            'shop_setting' => new ShopSettingResource($shopSetting),
            'valid_version' => new ShopSettingVersionResource($shopSetting->resolveValidVersion()),
        ]);
    }
}
