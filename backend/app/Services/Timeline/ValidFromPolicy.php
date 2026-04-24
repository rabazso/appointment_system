<?php

namespace App\Services\Timeline;

use Illuminate\Database\Eloquent\Model;

class ValidFromPolicy
{
    public static function for(Model $version): array
    {
        if (! $version->valid_from?->gt(now()->startOfDay())) {
            return [
                'editable' => false,
                'min' => null,
                'max' => null,
            ];
        }

        $previousVersion = self::previousVersion($version);
        $nextVersion = self::nextVersion($version);
        $min = $previousVersion?->valid_from?->copy()->addDay()->startOfDay();
        $tomorrow = now()->startOfDay()->addDay();

        if ($min === null || $min->lt($tomorrow)) {
            $min = $tomorrow;
        }

        return [
            'editable' => true,
            'min' => $min->toDateString(),
            'max' => $nextVersion?->valid_from?->copy()->subDay()->startOfDay()->toDateString(),
        ];
    }

    private static function previousVersion(Model $version): ?Model
    {
        return self::timelineQuery($version)
            ->where('valid_from', '<', $version->valid_from)
            ->orderByDesc('valid_from')
            ->first();
    }

    private static function nextVersion(Model $version): ?Model
    {
        return self::timelineQuery($version)
            ->where('valid_from', '>', $version->valid_from)
            ->orderBy('valid_from')
            ->first();
    }

    private static function timelineQuery(Model $version)
    {
        $ownerKey = array_key_exists('service_id', $version->getAttributes()) ? 'service_id' : 'employee_id';
        $modelClass = get_class($version);

        return $modelClass::query()
            ->where($ownerKey, $version->getAttribute($ownerKey))
            ->whereKeyNot($version->getKey());
    }
}
