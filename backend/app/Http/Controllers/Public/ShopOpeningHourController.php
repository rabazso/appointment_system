<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\IndexShopOpeningHoursRequest;
use App\Http\Resources\Shop\ShopOpeningHourResource;
use App\Models\ShopOpeningHour;

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
}
