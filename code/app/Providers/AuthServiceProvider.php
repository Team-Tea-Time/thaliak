<?php

namespace Thaliak\Providers;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'Thaliak\Model' => 'Thaliak\Policies\ModelPolicy',
    ];

    public function register()
    {
        Passport::ignoreMigrations();
    }

    public function boot()
    {
        $this->registerPolicies();

        // Passport configuration
        Passport::tokensExpireIn(
            Carbon::now()->addDays(
                config('auth.passport.expiration.access_token')
            )
        );
        Passport::refreshTokensExpireIn(
            Carbon::now()->addDays(
                config('auth.passport.expiration.refresh_token')
            )
        );
    }
}
