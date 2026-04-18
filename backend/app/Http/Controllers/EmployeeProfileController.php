<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeGalleryImageRequest;
use App\Http\Requests\UpdateEmployeeProfileRequest;
use App\Http\Resources\EmployeeImageResource;
use App\Http\Resources\EmployeeProfileResource;
use App\Models\EmployeeImage;
use App\Services\ImagePreviewService;
use Illuminate\Http\Request;

class EmployeeProfileController extends Controller
{
    public function show(Request $request): EmployeeProfileResource
    {
        $employee = $request->user()->employee;

        return new EmployeeProfileResource($employee->load(['profileImage', 'images']));
    }

    public function update(UpdateEmployeeProfileRequest $request): EmployeeProfileResource
    {
        $employee = $request->user()->employee;
        $employee->update($request->validated());

        return new EmployeeProfileResource($employee->load(['profileImage', 'images']));
    }

    public function storeProfilePic(StoreEmployeeGalleryImageRequest $request, ImagePreviewService $imagePreviewService): EmployeeProfileResource {
        $employee = $request->user()->employee;
        $file = $request->file('image');

        $employeeImage = EmployeeImage::create([
            'employee_id' => $employee->id,
            'type' => $file->getMimeType(),
            'original' => $file->get(),
            'preview' => $imagePreviewService->createFromFile($file),
        ]);

        $employee->update([
            'profile_image_id' => $employeeImage->id,
        ]);

        return new EmployeeProfileResource($employee->load(['profileImage', 'images']));
    }

    public function storeGalleryImg(StoreEmployeeGalleryImageRequest $request, ImagePreviewService $imagePreviewService): EmployeeImageResource {
        $employee = $request->user()->employee;
        $file = $request->file('image');

        $employeeImage = EmployeeImage::create([
            'employee_id' => $employee->id,
            'type' => $file->getMimeType(),
            'original' => $file->get(),
            'preview' => $imagePreviewService->createFromFile($file),
        ]);

        return new EmployeeImageResource($employeeImage);
    }

    public function destroyGalleryImg(Request $request, int $imgId)
    {
        $employee = $request->user()->employee;
        
        if ($employee->profile_image_id === $imgId) {
            return response()->json(['message' => 'Profile image cannot be deleted from gallery.']);
        }

        $img = $employee->images()->findOrFail($imgId);

        return $img->delete() ? response()->noContent() : abort(500);
    }
}
