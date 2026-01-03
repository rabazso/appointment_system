<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculation;

class AppointmentController extends Controller
{
    public function index(Request $request, AppointmentCalculation $calculation)
    {
        $appointments = $calculation->Appointments($request);

        return response()->json(
            $appointments,
        );
    }
}