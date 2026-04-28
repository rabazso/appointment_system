<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EmployeeImage;
use Illuminate\Http\Response;

class EmployeeImageController extends Controller
{
    public function showOriginal(EmployeeImage $employeeImage): Response
    {
        return response($employeeImage->original, headers: [
            'Content-Type' => $employeeImage->type,
        ]);
    }

    public function showPreview(EmployeeImage $employeeImage): Response
    {
        return response($employeeImage->preview, headers: [
            'Content-Type' => $employeeImage->type,
        ]);
    }
}
