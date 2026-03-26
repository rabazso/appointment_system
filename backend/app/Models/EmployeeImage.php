<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeImage extends Model
{
    protected $fillable = [
        'employee_id',
        'image_path',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        $url = Storage::disk('public')->url($this->image_path);
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        if ($baseUrl !== '' && !preg_match('#^https?://#', $baseUrl)) {
            $baseUrl = 'http://' . $baseUrl;
        }

        return $baseUrl === '' ? $url : $baseUrl . $url;
    }
}
