<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexServiceVersionsRequest;
use App\Http\Requests\StoreServiceVersionRequest;
use App\Http\Requests\UpdateServiceVersionRequest;
use App\Http\Resources\ServiceVersionResource;
use App\Models\Service;
use App\Models\ServiceVersion;
use App\Services\Timeline\VersionTimelineService;
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

    public function store(StoreServiceVersionRequest $request, VersionTimelineService $timelineService)
    {
        $validated = $request->validated();
        $service = Service::findOrFail($validated['service_id']);

        $version = $timelineService->createVersion($service->versions(), $validated);

        return new ServiceVersionResource($version);
    }

    public function update(UpdateServiceVersionRequest $request, ServiceVersion $serviceVersion,
    VersionTimelineService $timelineService): ServiceVersionResource
    {
        $serviceVersion = $timelineService->updateVersion($serviceVersion->service->versions(), 
        $serviceVersion, $request->validated());

        return new ServiceVersionResource($serviceVersion);
    }

    public function destroy(ServiceVersion $serviceVersion, VersionTimelineService $timelineService): JsonResponse
    {
        $timelineService->deleteVersion($serviceVersion->service->versions(), $serviceVersion);

        return response()->json(['message' => 'Service version deleted successfully']);
    }
}
