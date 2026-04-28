<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\ServiceAvailabilityRequest;
use App\Http\Resources\Service\ServiceAvailabilityResource;
use App\Models\Service;
use App\Models\ServiceVersion;
use App\Services\Timeline\VersionTimelineService;
use Illuminate\Http\JsonResponse;

class ServiceAvailabilityController extends Controller
{
    public function index(Service $service)
    {
        $today = now()->startOfDay();

        $availability = $service->versions()
            ->currentAndUpcomingFrom($today)
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
