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
        $validFrom = Carbon::parse($data['valid_from'])->startOfDay();

        $this->ensureUniqueValidFrom($timeline, $validFrom);

        $previousVersion = (clone $timeline)->orderByDesc('valid_from')->first();
        $minimumValidFrom = now()->startOfDay()->addDay();

        if ($previousVersion && $previousVersion->valid_from->copy()->addDay()->startOfDay()->gt($minimumValidFrom)) {
            $minimumValidFrom = $previousVersion->valid_from->copy()->addDay()->startOfDay();
        }

        if ($validFrom->lt($minimumValidFrom)) {
            throw ValidationException::withMessages([
                'valid_from' => 'The start date must be at least the next allowed day.',
            ]);
        }

        if ($previousVersion) {
            $previousVersion->update(['valid_to' => $validFrom]);
        }

        return $timeline->create([
            ...$data,
            'valid_from' => $validFrom,
            'valid_to' => null,
        ]);
    }

    public function updateVersion(HasMany $timeline, Model $version, array $data): Model
    {
        if (blank($data['valid_from'] ?? null)) {
            unset($data['valid_from']);

            $version->update($data);

            return $version->refresh();
        }

        $validFrom = Carbon::parse($data['valid_from'])->startOfDay();
        $this->validateValidFromUpdate($timeline, $version, $validFrom);

        $previousVersion = $this->previousBefore($timeline, $validFrom, $version);
        $nextVersion = $this->nextAfter($timeline, $validFrom, $version);

        if ($previousVersion) {
            $previousVersion->update(['valid_to' => $validFrom]);
        }

        $version->update([
            ...$data,
            'valid_from' => $validFrom,
            'valid_to' => $nextVersion?->valid_from,
        ]);

        return $version->refresh();
    }

    public function deleteVersion(HasMany $timeline, Model $version): void
    {
        $previousVersion = $this->previousBefore($timeline, $version->valid_from);
        $nextVersion = $this->nextAfter($timeline, $version->valid_from);

        if ($previousVersion) {
            $previousVersion->update(['valid_to' => $nextVersion?->valid_from]);
        }

        $version->delete();
    }

    public function previousBefore(HasMany $timeline, Carbon $validFrom, ?Model $excluding = null): ?Model
    {
        return (clone $timeline)
            ->when($excluding, fn ($query) => $query->whereKeyNot($excluding->getKey()))
            ->where('valid_from', '<', $validFrom)
            ->orderByDesc('valid_from')
            ->first();
    }

    public function nextAfter(HasMany $timeline, Carbon $validFrom, ?Model $excluding = null): ?Model
    {
        return (clone $timeline)
            ->when($excluding, fn ($query) => $query->whereKeyNot($excluding->getKey()))
            ->where('valid_from', '>', $validFrom)
            ->orderBy('valid_from')
            ->first();
    }

    private function validateValidFromUpdate(HasMany $timeline, Model $version, Carbon $validFrom): void
    {
        if (! $version->valid_from->gt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'valid_from' => 'The start date can only be changed for upcoming versions.',
            ]);
        }

        if (! $validFrom->gt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'valid_from' => 'The start date must stay in the future.',
            ]);
        }

        $this->ensureUniqueValidFrom($timeline, $validFrom, $version);

        $previousVersion = $this->previousBefore($timeline, $validFrom, $version);
        $nextVersion = $this->nextAfter($timeline, $validFrom, $version);

        if ($previousVersion && $validFrom->lt($previousVersion->valid_from->copy()->addDay()->startOfDay())) {
            throw ValidationException::withMessages([
                'valid_from' => 'The start date must be at least one day after the previous version start date.',
            ]);
        }

        if ($nextVersion && $validFrom->gt($nextVersion->valid_from->copy()->subDay()->startOfDay())) {
            throw ValidationException::withMessages([
                'valid_from' => 'The start date must be at least one day before the next version start date.',
            ]);
        }
    }

    private function ensureUniqueValidFrom(HasMany $timeline, Carbon $validFrom, ?Model $excluding = null): void
    {
        $exists = (clone $timeline)
            ->when($excluding, fn ($query) => $query->whereKeyNot($excluding->getKey()))
            ->whereDate('valid_from', $validFrom->toDateString())
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'valid_from' => 'A version with this start date already exists.',
            ]);
        }
    }
}
