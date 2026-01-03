<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculation;
use App\Http\Requests\AppointmentRequest;

class AppointmentController extends Controller
{
    public function index(AppointmentRequest $request, AppointmentCalculation $calculation)
    {
        $appointments = $calculation->Appointments($request);

        return response()->json(
            $appointments,
        );
    }
}