<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\AppointmentCalculator;

class AppointmentController extends Controller
{
    public function allAppointments(Request $request, AppointmentCalculator $calculator)
    {
        $options = $calculator->allAppointments($request);

        return response()->json(
            $options,
        );
    }

    public function AppointmentsByEmployee(Request $request, AppointmentCalculator $calculator)
    {
        $options = $calculator->appointmentsByEmployee($request);

        return response()->json(
            $options,
        );
    }
}