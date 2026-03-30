<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index()
    {
        return ServiceResource::collection(Service::all());
    }

    public function store(StoreServiceRequest $request)
    {
        $service = Service::create($request->validated());

        return new ServiceResource($service);
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
            'versions' => fn ($query) => $query->validAt($now),
        ])->get();

        return ServiceResource::collection($services);
    }
}
