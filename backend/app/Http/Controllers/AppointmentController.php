<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculation;
use App\Calculations\CreateAppointment;

use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AppointmentStoreRequest;

class AppointmentController extends Controller
{
    public function index(AppointmentRequest $request, AppointmentCalculation $calculation)
    {
        $appointments = $calculation->Appointments($request);

        return response()->json(
            $appointments,
        );
    }

    public function store(AppointmentStoreRequest $request, CreateAppointment $create)
    {
        return $create->Create($request);
    }
}