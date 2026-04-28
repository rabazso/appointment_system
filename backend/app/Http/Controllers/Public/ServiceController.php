<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Service\ServiceResource;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with([
            'versions' => fn ($query) => $query->validAt(now())->orderBy('valid_from'),
        ])->get();

        return ServiceResource::collection($services);
    }
}
