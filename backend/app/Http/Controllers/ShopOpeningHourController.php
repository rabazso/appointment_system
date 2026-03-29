<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexShopOpeningHoursRequest;
use App\Http\Requests\StoreShopOpeningHourRequest;
use App\Http\Requests\UpdateShopOpeningHourRequest;
use App\Http\Resources\ShopOpeningHourResource;
use App\Models\ShopOpeningHour;
use Illuminate\Http\JsonResponse;

class ShopOpeningHourController extends Controller
{
    public function index(IndexShopOpeningHoursRequest $request)
    {
        $validated = $request->validated();
        $weekday = $validated['weekday'] ?? null;

        $openingHours = ShopOpeningHour::query()
            ->when(
                $weekday !== null,
                fn ($query) => $query->where('weekday', $weekday)
            )
            ->get();

        return ShopOpeningHourResource::collection($openingHours);
    }

    public function store(StoreShopOpeningHourRequest $request): ShopOpeningHourResource
    {
        $openingHour = ShopOpeningHour::create($request->validated());

        return new ShopOpeningHourResource($openingHour);
    }

    public function update(UpdateShopOpeningHourRequest $request, ShopOpeningHour $shopOpeningHour): ShopOpeningHourResource
    {
        $shopOpeningHour->update($request->validated());

        return new ShopOpeningHourResource($shopOpeningHour);
    }

    public function destroy(ShopOpeningHour $shopOpeningHour): JsonResponse
    {
        $shopOpeningHour->delete();

        return response()->json(['message' => 'Shop opening hour deleted successfully']);
    }
}
