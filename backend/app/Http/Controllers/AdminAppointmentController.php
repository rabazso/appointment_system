<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCancelAppointmentRequest;
use App\Http\Resources\AppointmentStatusResource;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function cancel(AdminCancelAppointmentRequest $request, Appointment $appointment)
    {
        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            return response()->json([
                'message' => 'Only pending or confirmed appointments can be cancelled',
            ], 422);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => trim($request->validated('cancellation_reason')),
            'cancelled_by' => 'admin',
        ]);

        return new AppointmentStatusResource($appointment);
    }

    public function complete(Request $request, Appointment $appointment)
    {
        if ($appointment->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed appointments can be completed',
            ], 422);
        }

        $appointment->update([
            'status' => 'completed',
        ]);

        return new AppointmentStatusResource($appointment);
    }

    public function markNoShow(Request $request, Appointment $appointment)
    {
        if ($appointment->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed appointments can be marked as no-show',
            ], 422);
        }

        $appointment->update([
            'status' => 'no_show',
        ]);

        return new AppointmentStatusResource($appointment);
    }
}
