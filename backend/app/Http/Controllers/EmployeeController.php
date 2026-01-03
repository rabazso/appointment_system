<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\EmployeeCalculation;
use App\Http\Requests\EmployeeRequest;

class EmployeeController extends Controller
{
    public function index(EmployeeRequest $request, EmployeeCalculation $calculation)
    {
        $employees = $calculation->Employees($request);

        return response()->json(
            $employees,
        );
    }
}
