<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceVersionAtRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceVersionResource;
use Illuminate\Http\Request;
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

    public function currentVersions(){
        $services = Service::all();

        $payload = $services->map(function (Service $service) {
                return [
                    'service' => new ServiceResource($service),
                    'current_version' => new ServiceVersionResource($service->resolveCurrentVersion()),
                ];
            });
        return response()->json($payload);
    }

    public function versionAt(ServiceVersionAtRequest $request, Service $service)
    {
        return response()->json([
            'service' => new ServiceResource($service),
            'version' => new ServiceVersionResource($service->resolveVersionAt($request->validated()['date'])),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
