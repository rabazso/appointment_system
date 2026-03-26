<?php
namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function configurationItems()
    {
        return $this->hasMany(EmployeeServiceConfigurationItem::class);
    }

    public function versions()
    {
        return $this->hasMany(ServiceVersion::class);
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function resolveVersionAt(?string $at = null): ?ServiceVersion
    {
        $at = $at ? Carbon::parse($at) : now();

        return $this->versions()->validAt($at)->first();
    }

    public function resolveCurrentVersion(): ?ServiceVersion
    {
        return $this->resolveVersionAt();
    }
}
