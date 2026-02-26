<?php

namespace App\Http\Controllers;

use App\Calculations\EmployeeCalculation;
use App\Http\Requests\EmployeeRequest;
use App\Models\EmployeeGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(EmployeeRequest $request, EmployeeCalculation $calculation)
    {
        $employees = $calculation->Employees($request);

        return response()->json(
            $employees,
        );
    }

    public function barberProfile(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        $employee->load(['user:id,name', 'gallery:id,employee_id,image_url']);

        return response()->json([
            'id' => $employee->id,
            'name' => $employee->user?->name,
            'description' => $employee->bio,
            'photo_url' => $employee->photo_url,
            'gallery' => $employee->gallery
                ->sortByDesc('id')
                ->values()
                ->map(fn (EmployeeGallery $item) => [
                    'id' => $item->id,
                    'image_url' => $item->image_url,
                ]),
        ]);
    }

    public function updateBarberProfile(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $request->user()->forceFill([
            'name' => $validated['name'],
        ])->save();

        $employee->bio = $validated['description'] ?? null;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('images/' . $employee->id, 'public');
            $photoUrl = $this->publicUrlFromPath($path);
            $employee->photo_url = $photoUrl;
            $employee->gallery()->create([
                'image_url' => $photoUrl,
            ]);
        }

        $employee->save();

        return $this->barberProfile($request);
    }

    public function uploadBarberGalleryImage(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $path = $validated['image']->store('images/' . $employee->id, 'public');
        $gallery = $employee->gallery()->create([
            'image_url' => $this->publicUrlFromPath($path),
        ]);

        return response()->json([
            'id' => $gallery->id,
            'image_url' => $gallery->image_url,
        ], 201);
    }

    public function deleteBarberGalleryImage(Request $request, EmployeeGallery $gallery)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Barber profile not found'], 404);
        }

        if ($gallery->employee_id !== $employee->id) {
            return response()->json(['message' => 'You are not allowed to delete this image'], 403);
        }

        $storedPath = $this->extractPublicPathFromUrl($gallery->image_url);
        if ($storedPath) {
            Storage::disk('public')->delete($storedPath);
        }

        $gallery->delete();

        return response()->json(['message' => 'Image deleted']);
    }

    private function publicUrlFromPath(string $path): string
    {
        $relative = Storage::disk('public')->url($path);

        if (Str::startsWith($relative, ['http://', 'https://'])) {
            return $relative;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        if ($baseUrl !== '' && !preg_match('#^https?://#', $baseUrl)) {
            $baseUrl = 'http://' . $baseUrl;
        }

        return $baseUrl === '' ? $relative : $baseUrl . $relative;
    }

    private function extractPublicPathFromUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $parts = parse_url($url);
        $path = $parts['path'] ?? null;
        if (!$path) {
            return null;
        }

        $storagePrefix = '/storage/';
        $position = strpos($path, $storagePrefix);
        if ($position === false) {
            return null;
        }

        return substr($path, $position + strlen($storagePrefix));
    }
}
