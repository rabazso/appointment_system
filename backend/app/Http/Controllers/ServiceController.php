<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexServiceVersionsRequest;
use App\Http\Requests\ShowServiceValidVersionAtRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceVersionResource;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ServiceResource::collection(Service::all());
    }

    public function indexServicesWithValidVersion()
    {
        $services = Service::all();

        $payload = $services->map(function (Service $service) {
            return [
                'service' => new ServiceResource($service),
                'valid_version' => new ServiceVersionResource($service->resolveValidVersion()),
            ];
        });

        return response()->json($payload);
    }

    public function indexServiceVersions(IndexServiceVersionsRequest $request)
    {
        $service = Service::findOrFail($request->validated()['service_id']);

        return ServiceVersionResource::collection(
            $service->versions
        );
    }

    public function showServiceValidVersionAt(ShowServiceValidVersionAtRequest $request)
    {
        $service = Service::findOrFail($request->validated()['service_id']);
        $date = $request->validated()['date'];

        return response()->json([
            'service' => new ServiceResource($service),
            'valid_version' => new ServiceVersionResource($service->resolveValidVersionAt($date)),
        ]);
    }
}
