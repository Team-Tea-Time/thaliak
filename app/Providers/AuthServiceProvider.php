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

        Passport::routes();
        Passport::tokensExpireIn(Carbon::now()->addDays(7));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(14));
        Passport::pruneRevokedTokens();

        // OAuth token proxy routes - these are used to append client
        // credentials to Password Grant requests
        Route::post(
            'oauth/proxy/token',
            'Thaliak\Http\Controllers\Oauth\ProxyController@getToken'
        );
        Route::post(
            'oauth/proxy/token/refresh',
            'Thaliak\Http\Controllers\Oauth\ProxyController@refreshToken'
        );
    }
}
