<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEmployeeImagesRequest;
use App\Http\Requests\StoreEmployeeImageRequest;
use App\Http\Resources\EmployeeImageResource;
use App\Models\EmployeeImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

    public function store(StoreEmployeeImageRequest $request): EmployeeImageResource
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'];
        $file = $request->file('image');
        $preview = $this->createPreview($file);

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

    private function createPreview($file): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->get());
        $image->scaleDown(width: 300, height: 300);

        return (string) $image->encode();
    }
}
