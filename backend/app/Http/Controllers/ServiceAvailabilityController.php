<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceAvailabilityRequest;
use App\Http\Resources\ServiceAvailabilityResource;
use App\Models\Service;
use App\Models\ServiceVersion;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;

class ServiceAvailabilityController extends Controller
{
    public function index(Service $service)
    {
        $availability = $service->versions()
            ->orderBy('valid_from')
            ->get();

        return ServiceAvailabilityResource::collection($availability);
    }

    public function store(
        ServiceAvailabilityRequest $request,
        Service $service,
        VersionTimelineService $timelineService
    ): ServiceAvailabilityResource {
        $availability = $timelineService->createVersion(
            $service->versions(),
            $request->validated()
        );

        return new ServiceAvailabilityResource($availability);
    }

    public function update(
        ServiceAvailabilityRequest $request,
        ServiceVersion $availability,
        VersionTimelineService $timelineService
    ): ServiceAvailabilityResource {
        $availability = $timelineService->updateVersion(
            $availability->service->versions(),
            $availability,
            $request->validated()
        );

        return new ServiceAvailabilityResource($availability);
    }

    public function destroy(ServiceVersion $availability, VersionTimelineService $timelineService): JsonResponse
    {
        $timelineService->deleteVersion($availability->service->versions(), $availability);

        return response()->json(['message' => 'Service availability deleted successfully']);
    }

}
