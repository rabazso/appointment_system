<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Http\Resources\Service\ServiceResource;
use App\Models\Service;
use App\Models\ServiceVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function store(StoreServiceRequest $request)
    {
        $service = DB::transaction(function () use ($request) {
            $service = Service::create($request->validated());

            ServiceVersion::create([
                'service_id' => $service->id,
                'is_available' => false,
                'valid_from' => now()->startOfDay(),
                'valid_to' => null,
            ]);

            return $service;
        });

        return new ServiceResource($service->load([
            'versions' => fn ($query) => $query->validAt(now())->orderBy('valid_from'),
        ]));
    }

    public function update(UpdateServiceRequest $request, Service $service): ServiceResource
    {
        $service->update($request->validated());

        return new ServiceResource($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }
}
