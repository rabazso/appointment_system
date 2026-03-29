<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexShopSettingVersionsRequest;
use App\Http\Requests\StoreShopSettingVersionRequest;
use App\Http\Requests\UpdateShopSettingVersionRequest;
use App\Http\Resources\ShopSettingVersionResource;
use App\Models\ShopSettingVersion;
use Illuminate\Http\JsonResponse;

class ShopSettingVersionController extends Controller
{
    public function index(IndexShopSettingVersionsRequest $request)
    {
        $validated = $request->validated();
        $shopSettingId = $validated['shop_setting_id'] ?? null;

        $versions = ShopSettingVersion::query()
            ->when(
                $shopSettingId,
                fn ($query) => $query->where('shop_setting_id', $shopSettingId)
            )
            ->get();

        return ShopSettingVersionResource::collection($versions);
    }

    public function store(StoreShopSettingVersionRequest $request): ShopSettingVersionResource
    {
        $version = ShopSettingVersion::create($request->validated());

        return new ShopSettingVersionResource($version);
    }

    public function update(
        UpdateShopSettingVersionRequest $request,
        ShopSettingVersion $shopSettingVersion
    ): ShopSettingVersionResource {
        $shopSettingVersion->update($request->validated());

        return new ShopSettingVersionResource($shopSettingVersion);
    }

    public function destroy(ShopSettingVersion $shopSettingVersion): JsonResponse
    {
        $shopSettingVersion->delete();

        return response()->json(['message' => 'Shop setting version deleted successfully']);
    }
}
