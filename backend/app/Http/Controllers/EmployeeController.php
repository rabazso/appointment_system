<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\EmployeeVersionResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index()
    {
        return EmployeeResource::collection(Employee::with('profileImage')->get());
    }

    public function store(StoreEmployeeRequest $request): EmployeeResource
    {
        $employee = Employee::create($request->validated());

        return new EmployeeResource($employee->load('profileImage'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): EmployeeResource
    {
        $employee->update($request->validated());

        return new EmployeeResource($employee->load('profileImage'));
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }

    public function indexEmployeesWithValidVersion()
    {
        $employees = Employee::with('profileImage')->get();

        $payload = $employees->map(function (Employee $employee) {
            return [
                'employee' => new EmployeeResource($employee),
                'valid_version' => new EmployeeVersionResource($employee->resolveValidVersion()),
            ];
        });

        return response()->json($payload);
    }
}
