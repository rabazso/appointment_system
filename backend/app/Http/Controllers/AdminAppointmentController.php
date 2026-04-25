<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCancelAppointmentRequest;
use App\Http\Resources\AppointmentStatusResource;
use App\Models\Appointment;

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
}
