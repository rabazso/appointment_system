<?php

namespace App\Http\Controllers;

use App\Calculations\EmployeeCalculation;
use App\Http\Resources\BarberProfileResource;
use App\Http\Resources\EmployeeGalleryResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\UpdateBarberProfileRequest;
use App\Http\Requests\UploadBarberGalleryImageRequest;
use App\Models\EmployeeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(EmployeeRequest $request, EmployeeCalculation $calculation)
    {
        $employees = $calculation->Employees($request);

        return EmployeeResource::collection($employees);
    }

    public function barberProfile(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        $employee->load([
            'gallery' => fn ($query) => $query->select('id', 'employee_id', 'image_path')->orderByDesc('id'),
        ]);

        return new BarberProfileResource($employee);
    }

    public function updateBarberProfile(UpdateBarberProfileRequest $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        $validated = $request->validated();

        $employee->name = $validated['name'];
        $employee->bio = $validated['description'] ?? null;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('images/' . $employee->id, 'public');
            $employee->photo_path = $path;
            $employee->gallery()->create([
                'image_path' => $path,
            ]);
        }

        $employee->save();

        return $this->barberProfile($request);
    }

    public function uploadBarberGalleryImage(UploadBarberGalleryImageRequest $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        $validated = $request->validated();

        $path = $validated['image']->store('images/' . $employee->id, 'public');
        $gallery = $employee->gallery()->create([
            'image_path' => $path,
        ]);

        return (new EmployeeGalleryResource($gallery))
            ->response()
            ->setStatusCode(201);
    }

    public function deleteBarberGalleryImage(Request $request, EmployeeImage $gallery)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        if ($gallery->employee_id !== $employee->id) {
            return response()->json(['message' => 'You are not allowed to delete this image'], 403);
        }

        if ($gallery->image_path) {
            Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();

        return response()->json(['message' => 'Image deleted']);
    }
}
