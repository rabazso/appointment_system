<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexShopSpecialDaysRequest;
use App\Http\Requests\StoreShopSpecialDayRequest;
use App\Http\Requests\UpdateShopSpecialDayRequest;
use App\Http\Resources\ShopSpecialDayResource;
use App\Models\ShopSpecialDay;
use Carbon\Carbon;
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

    public function indexForMonth(IndexShopSpecialDaysRequest $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $startOfMonth = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $specialDays = ShopSpecialDay::query()
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date')
            ->get();

        return ShopSpecialDayResource::collection($specialDays);
    }

    public function showByDate(IndexShopSpecialDaysRequest $request): JsonResponse|ShopSpecialDayResource
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $specialDay = ShopSpecialDay::query()
            ->whereDate('date', $validated['date'])
            ->first();

        if (! $specialDay) {
            return response()->json(['data' => null]);
        }

        return new ShopSpecialDayResource($specialDay);
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
