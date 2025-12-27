<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return Appointment::all()->makeHidden(['customer_id']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'service_id' => 'required|exists:services,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date',
        ]);

        $data['customer_id'] = Auth::id();

        $appointment = Appointment::create($data);
        return response()->json($appointment);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
