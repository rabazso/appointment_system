<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexShopSpecialDaysRequest;
use App\Http\Requests\StoreShopSpecialDayRequest;
use App\Http\Requests\UpdateShopSpecialDayRequest;
use App\Http\Resources\ShopSpecialDayResource;
use App\Models\ShopSpecialDay;
use Illuminate\Http\JsonResponse;

class ShopSpecialDayController extends Controller
{
    public function index(IndexShopSpecialDaysRequest $request)
    {
        $validated = $request->validated();
        $date = $validated['date'] ?? null;
        $name = $validated['name'] ?? null;

        $specialDays = ShopSpecialDay::query()
            ->when(
                $date,
                fn ($query) => $query->whereDate('date', $date)
            )
            ->when(
                $name,
                fn ($query) => $query->where('name', 'like', '%' . $name . '%')
            )
            ->get();

        return ShopSpecialDayResource::collection($specialDays);
    }

    public function store(StoreShopSpecialDayRequest $request): ShopSpecialDayResource
    {
        $specialDay = ShopSpecialDay::create($request->validated());

        return new ShopSpecialDayResource($specialDay);
    }

    public function update(UpdateShopSpecialDayRequest $request, ShopSpecialDay $shopSpecialDay): ShopSpecialDayResource
    {
        $shopSpecialDay->update($request->validated());

        return new ShopSpecialDayResource($shopSpecialDay);
    }

    public function destroy(ShopSpecialDay $shopSpecialDay): JsonResponse
    {
        $shopSpecialDay->delete();

        return response()->json(['message' => 'Shop special day deleted successfully']);
    }
}
