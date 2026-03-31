<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeImagesRequest;
use App\Http\Requests\StoreEmployeeImageRequest;
use App\Http\Resources\EmployeeImageResource;
use App\Models\EmployeeImage;
use App\Services\ImagePreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EmployeeImageController extends Controller
{
    public function index(IndexEmployeeImagesRequest $request)
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'] ?? null;

        $images = EmployeeImage::query()
            ->when(
                $employeeId,
                fn ($query) => $query->where('employee_id', $employeeId)
            )
            ->whereDoesntHave('employee', fn ($query) =>
                $query->whereColumn('employees.profile_image_id', 'employee_images.id')
            )
            ->get();

        return EmployeeImageResource::collection($images);
    }

    public function store(
        StoreEmployeeImageRequest $request,
        ImagePreviewService $imagePreviewService
    ): EmployeeImageResource
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'];
        $file = $request->file('image');
        $preview = $imagePreviewService->createFromFile($file);

        $employeeImage = EmployeeImage::create([
            'employee_id' => $employeeId,
            'type' => $file->getMimeType(),
            'original' => $file->get(),
            'preview' => $preview,
        ]);

        return new EmployeeImageResource($employeeImage);
    }

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

    public function destroy(EmployeeImage $employeeImage): JsonResponse
    {
        $employeeImage->delete();

        return response()->json([
            'message' => 'Employee image deleted successfully',
        ]);
    }
}
