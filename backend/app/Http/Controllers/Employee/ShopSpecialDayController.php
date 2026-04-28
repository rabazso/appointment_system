<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\IndexShopSpecialDaysRequest;
use App\Http\Resources\Shop\ShopSpecialDayResource;
use App\Models\ShopSpecialDay;
use Carbon\Carbon;

class ShopSpecialDayController extends Controller
{
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
}
