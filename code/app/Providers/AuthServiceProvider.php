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

        // Override Passport's token issuing/refreshing routes to apply the
        // following middleware to them:
        // handle-grant-injections: injects the client secret into password and
        // refresh_token grant type requests according to the supplied client
        // ID.
        // attach-token-cookie: attaches a cookie containing the oauth token to
        // a successful response.
        Route::post('oauth/token', [
            'middleware' => ['handle-grant-injections', 'attach-token-cookie'],
            'uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken'
        ]);
        Route::post('oauth/token/refresh', [
            'middleware' => ['handle-grant-injections', 'attach-token-cookie'],
            'uses' => '\Laravel\Passport\Http\Controllers\TransientTokenController@refresh'
        ]);
    }
}
