<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculator;

class AppointmentController extends Controller
{
    public function index(Request $request, AppointmentCalculator $calculator)
    {
        $appointments = $calculator->Appointments($request);

        return response()->json(
            $appointments,
        );
    }
}