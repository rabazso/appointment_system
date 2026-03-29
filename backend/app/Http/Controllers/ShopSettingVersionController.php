<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexShopSettingVersionsRequest;
use App\Http\Requests\StoreShopSettingVersionRequest;
use App\Http\Requests\UpdateShopSettingVersionRequest;
use App\Http\Resources\ShopSettingVersionResource;
use App\Models\ShopSetting;
use App\Models\ShopSettingVersion;
use App\Services\Timeline\VersionTimelineService;
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

    public function store(StoreShopSettingVersionRequest $request, VersionTimelineService $timelineService): ShopSettingVersionResource
    {
        $validated = $request->validated();
        $shopSetting = ShopSetting::findOrFail($validated['shop_setting_id']);

        $version = $timelineService->createVersion($shopSetting->versions(), $validated);

        return new ShopSettingVersionResource($version);
    }

    public function update(UpdateShopSettingVersionRequest $request, ShopSettingVersion $shopSettingVersion, 
    VersionTimelineService $timelineService): ShopSettingVersionResource
    {
        $shopSettingVersion = $timelineService->updateVersion($shopSettingVersion->shopSetting->versions(), 
        $shopSettingVersion, $request->validated());

        return new ShopSettingVersionResource($shopSettingVersion);
    }

    public function destroy(ShopSettingVersion $shopSettingVersion, VersionTimelineService $timelineService): JsonResponse
    {
        $timelineService->deleteVersion($shopSettingVersion->shopSetting->versions(), $shopSettingVersion);

        return response()->json(['message' => 'Shop setting version deleted successfully']);
    }
}
