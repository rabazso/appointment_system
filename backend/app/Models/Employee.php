<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'bio',
        'photo_path',
        'instagram_url',
    ];

    public function versions()
    {
        return $this->hasMany(EmployeeVersion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function configurations()
    {
        return $this->hasMany(EmployeeServiceConfiguration::class);
    }

    public function images()
    {
        return $this->hasMany(EmployeeImage::class);
    }

    public function gallery()
    {
        return $this->hasMany(EmployeeImage::class);
    }

    public function workingHours()
    {
        return $this->hasMany(EmployeeWorkingHour::class);
    }

    public function breaks()
    {
        return $this->hasMany(EmployeeBreak::class);
    }

    public function timeOffRequests()
    {
        return $this->hasMany(EmployeeTimeOff::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->publicUrlForPath($this->photo_path);
    }

    private function publicUrlForPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $url = Storage::disk('public')->url($path);
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
