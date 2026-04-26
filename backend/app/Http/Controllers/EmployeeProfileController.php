<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeGalleryImageRequest;
use App\Http\Requests\UpdateEmployeeProfileRequest;
use App\Http\Resources\EmployeeImageResource;
use App\Http\Resources\EmployeeProfileResource;
use App\Models\EmployeeImage;
use App\Services\ImagePreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        return response()->json(
            (new EmployeeProfileResource($employee->load(['profileImage', 'images'])))->toArray($request)
        );
    }

    public function update(UpdateEmployeeProfileRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        $data = $request->validated();

        if (array_key_exists('description', $data)) {
            $data['bio'] = $data['description'];
            unset($data['description']);
        }

        $employee->update($data);

        return response()->json(
            (new EmployeeProfileResource($employee->load(['profileImage', 'images'])))->toArray($request)
        );
    }

    public function storeProfilePic(StoreEmployeeGalleryImageRequest $request, ImagePreviewService $imagePreviewService): JsonResponse {
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

        return response()->json(
            (new EmployeeProfileResource($employee->load(['profileImage', 'images'])))->toArray($request)
        );
    }

    public function storeGalleryImg(StoreEmployeeGalleryImageRequest $request, ImagePreviewService $imagePreviewService): JsonResponse {
        $employee = $request->user()->employee;
        $file = $request->file('image');

        $employeeImage = EmployeeImage::create([
            'employee_id' => $employee->id,
            'type' => $file->getMimeType(),
            'original' => $file->get(),
            'preview' => $imagePreviewService->createFromFile($file),
        ]);

        return response()->json((new EmployeeImageResource($employeeImage))->toArray($request));
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
