<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceRootUrl(config('app.url'));

        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            $relativeSignedUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ],
                false
            );

            $backendBase = rtrim((string) config('app.url'), '/');
            if ($backendBase !== '' && !preg_match('#^https?://#', $backendBase)) {
                $backendBase = 'http://' . $backendBase;
            }

            return $backendBase . $relativeSignedUrl;
        });

        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            $frontendBase = rtrim((string) config('app.frontend_url'), '/');
            if ($frontendBase !== '' && !preg_match('#^https?://#', $frontendBase)) {
                $frontendBase = 'http://' . $frontendBase;
            }

            $query = http_build_query([
                'email' => $user->getEmailForPasswordReset(),
                'token' => $token,
            ]);

            return $frontendBase . '/reset-password?' . $query;
        });

        Carbon::setLocale(config('app.locale'));
        date_default_timezone_set(config('app.timezone'));
    }
}
