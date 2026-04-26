<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'last_used_at',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function issueForUser(User $user, DateTimeInterface $expiresAt): string
    {
        $user->apiTokens()->delete();

        $plainToken = Str::random(80);

        static::query()->create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
            'last_used_at' => now(),
        ]);

        return $plainToken;
    }

    public static function findValidByPlainText(string $plainToken): ?self
    {
        $apiToken = static::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->with('user')
            ->first();

        if (!$apiToken) {
            return null;
        }

        if ($apiToken->expires_at && $apiToken->expires_at->isPast()) {
            $apiToken->delete();

            return null;
        }

        $apiToken->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $apiToken;
    }
}
