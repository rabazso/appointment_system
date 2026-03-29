<?php

namespace App\Services\Timeline;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class VersionTimelineService
{
    public function createVersion(HasMany $timeline, array $data): Model
    {
        $validFrom = Carbon::parse($data['valid_from']);
        $validTo = array_key_exists('valid_to', $data) && $data['valid_to'] ? Carbon::parse($data['valid_to']) : null;

        if ($timeline->where('valid_from', $validFrom)->exists()) {
            throw ValidationException::withMessages([
                'valid_from' => 'A version with this start date already exists.',
            ]);
        }

        $currentVersion = $timeline->validAt($validFrom)->first();

        $nextVersion = $timeline->where('valid_from', '>', $validFrom)->orderBy('valid_from')->first();

        if ($validTo !== null && ! $validTo->isAfter($validFrom)) {
            throw ValidationException::withMessages([
                'valid_to' => 'The end date must be later than the start date.',
            ]);
        }

        if ($nextVersion && ($validTo === null || $validTo->isAfter($nextVersion->valid_from))) {
            $validTo = $nextVersion->valid_from;
        }

        if ($currentVersion) {
            $currentVersion->update([
                'valid_to' => $validFrom,
            ]);
        }

        return $timeline->create([
            ...$data,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
        ]);
    }

    public function updateVersion(HasMany $timeline, Model $version, array $data): Model
    {
        $validFrom = $version->valid_from;
        $validTo = $version->valid_to;

        if (array_key_exists('valid_from', $data)) {
            $validFrom = Carbon::parse($data['valid_from']);
        }

        if (array_key_exists('valid_to', $data)) {
            $validTo = $data['valid_to'] ? Carbon::parse($data['valid_to']) : null;
        }

        $previousVersion = $timeline->where('valid_from', '<', $version->valid_from)->orderByDesc('valid_from')->first();

        $nextVersion = $timeline->where('valid_from', '>', $version->valid_from)->orderBy('valid_from')->first();

        if ($previousVersion && ! $validFrom->isAfter($previousVersion->valid_from)) {
            throw ValidationException::withMessages([
                'valid_from' => 'The start date must be later than the previous version start date.',
            ]);
        }

        if ($nextVersion && ! $nextVersion->valid_from->isAfter($validFrom)) {
            throw ValidationException::withMessages([
                'valid_from' => 'The start date must be earlier than the next version start date.',
            ]);
        }

        if ($validTo !== null && ! $validTo->isAfter($validFrom)) {
            throw ValidationException::withMessages([
                'valid_to' => 'The end date must be later than the start date.',
            ]);
        }

        if ($nextVersion && ($validTo === null || $validTo->isAfter($nextVersion->valid_from))) {
            $validTo = $nextVersion->valid_from;
        }

        if ($previousVersion && ($previousVersion->valid_to === null || $previousVersion->valid_to->equalTo($version->valid_from) || $previousVersion->valid_to->isAfter($validFrom))) {
            $previousVersion->update([
                'valid_to' => $validFrom,
            ]);
        }

        $version->update([
            ...$data,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
        ]);

        return $version->refresh();
    }

    public function deleteVersion(HasMany $timeline, Model $version): void
    {
        $previousVersion = $timeline
            ->where('valid_from', '<', $version->valid_from)
            ->orderByDesc('valid_from')
            ->first();

        $nextVersion = $timeline
            ->where('valid_from', '>', $version->valid_from)
            ->orderBy('valid_from')
            ->first();

        if (
            $previousVersion &&
            ($previousVersion->valid_to === null || $previousVersion->valid_to->equalTo($version->valid_from))
        ) {
            $previousVersion->update([
                'valid_to' => $nextVersion?->valid_from,
            ]);
        }

        $version->delete();
    }
}
