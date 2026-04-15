<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeAppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request){
        $employee = $request->user()->employee;
        $statuses = $request->statuses ?? [];

        $appointments = Appointment::where('employee_id', $employee->id)
            ->with(['customer', 'appointmentServices.service'])
            ->whereDate('start_datetime', $request->date)->orderBy('start_datetime');

        if (!empty($statuses)) {
            $appointments->whereIn('status', $statuses);
        }

        return EmployeeAppointmentResource::collection($appointments->get());
    }
}
