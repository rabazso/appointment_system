<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\ServiceVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with([
            'versions' => fn ($query) => $query->validAt(now())->orderBy('valid_from'),
        ])->get();

        return ServiceResource::collection($services);
    }

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

    public function indexServicesWithValidVersion()
    {
        $now = now();

        $services = Service::with([
            'versions' => fn ($query) => $query->validAt($now)->orderBy('valid_from'),
        ])->get();

        return ServiceResource::collection($services);
    }
}
