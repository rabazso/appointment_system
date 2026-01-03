<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculations\EmployeeCalculation;

class EmployeeController extends Controller
{
    public function index(Request $request, EmployeeCalculation $calculation)
    {
        $employees = $calculation->Employees($request);

        return response()->json(
            $employees,
        );
    }
}
