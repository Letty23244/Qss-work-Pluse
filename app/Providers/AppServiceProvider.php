<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Password-reset emails point to the React frontend.
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $front = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
            return $front.'/reset-password?token='.$token.'&email='.urlencode($user->getEmailForPasswordReset());
        });
    }
}
