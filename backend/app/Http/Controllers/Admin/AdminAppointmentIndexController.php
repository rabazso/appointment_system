<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\AdminAppointmentIndexRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Models\Appointment;

class AdminAppointmentIndexController extends Controller
{
    public function index(AdminAppointmentIndexRequest $request)
    {
        $validated = $request->validated();
        $search = trim((string) ($validated['search'] ?? ''));
        $serviceId = $validated['service_id'] ?? null;
        $employeeId = $validated['employee_id'] ?? null;
        $status = $validated['status'] ?? null;
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        $orderBy = $validated['order_by'] ?? 'start_datetime';
        $orderDirection = $validated['order_direction'] ?? 'desc';

        $appointments = Appointment::query()
            ->with([
                'appointmentServices.service:id,name',
                'employee:id,name',
                'customer:id,name,email',
            ])
            ->when(
                $serviceId,
                fn ($query) => $query->whereHas(
                    'appointmentServices',
                    fn ($serviceQuery) => $serviceQuery->where('service_id', $serviceId)
                )
            )
            ->when(
                $employeeId,
                fn ($query) => $query->where('employee_id', $employeeId)
            )
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                $dateFrom,
                fn ($query) => $query->whereDate('start_datetime', '>=', $dateFrom)
            )
            ->when(
                $dateTo,
                fn ($query) => $query->whereDate('start_datetime', '<=', $dateTo)
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->whereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('employee', fn ($employeeQuery) => $employeeQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas(
                            'appointmentServices.service',
                            fn ($serviceQuery) => $serviceQuery->where('name', 'like', '%' . $search . '%')
                        );
                });
            })
            ->orderBy(
                match ($orderBy) {
                    'created_at' => 'created_at',
                    'price' => 'total_price',
                    'status' => 'status',
                    'customer' => 'customer_id',
                    'employee' => 'employee_id',
                    default => 'start_datetime',
                },
                $orderDirection
            )
            ->get();

        return AppointmentResource::collection($appointments);
    }
}
