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

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Passport routes and configuration
        Passport::routes();
        Passport::tokensExpireIn(Carbon::now()->addDays(7));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(14));
        Passport::pruneRevokedTokens();

        // Override Passport's token issuing/refreshing routes to apply the
        // InjectPasswordGrantCredentials middleware to them. This is to
        // prevent the need for passing a client ID and secret from the frontend
        // in the case of Password Grant requests.
        Route::post('oauth/token', [
            'middleware' => 'password-grant',
            'uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken'
        ]);
        Route::post('oauth/token/refresh', [
            'middleware' => ['web', 'auth', 'password-grant'],
            'uses' => '\Laravel\Passport\Http\Controllers\TransientTokenController@refresh'
        ]);
    }
}
