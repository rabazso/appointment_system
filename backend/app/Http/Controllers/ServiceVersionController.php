<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexServiceVersionsRequest;
use App\Http\Requests\ShowServiceVersionValidAtRequest;
use App\Http\Requests\StoreServiceVersionRequest;
use App\Http\Requests\UpdateServiceVersionRequest;
use App\Http\Resources\ServiceVersionResource;
use App\Models\ServiceVersion;
use Illuminate\Http\JsonResponse;

class ServiceVersionController extends Controller
{
    public function index(IndexServiceVersionsRequest $request)
    {
        $validated = $request->validated();
        $serviceId = $validated['service_id'] ?? null;

        $versions = ServiceVersion::when(
            $serviceId,
            fn($query) => $query->where('service_id', $serviceId)
        )
            ->get();

        return ServiceVersionResource::collection($versions);
    }

    public function store(StoreServiceVersionRequest $request): JsonResponse
    {
        $version = ServiceVersion::create($request->validated());

        return new ServiceVersionResource($version);
    }

    public function update(UpdateServiceVersionRequest $request, ServiceVersion $serviceVersion): ServiceVersionResource
    {
        $serviceVersion->update($request->validated());

        return new ServiceVersionResource($serviceVersion);
    }

    public function destroy(ServiceVersion $serviceVersion): JsonResponse
    {
        $serviceVersion->delete();

        return response()->json(['message' => 'Service version deleted successfully']);
    }

    public function ShowServiceVersionValidAt(ShowServiceVersionValidAtRequest $request)
    {
        $validated = $request->validated();
        $serviceId = $validated['service_id'];
        $date = Carbon::parse($validated['date']);

        $version = ServiceVersion::query()
            ->validAt($date)
            ->where('service_id', $serviceId)
            ->first();

        return new ServiceVersionResource($version);
    }
}
