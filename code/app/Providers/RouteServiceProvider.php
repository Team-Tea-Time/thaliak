<?php

namespace Thaliak\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Socialite;
use Thaliak\Models\Character;
use Thaliak\Models\OAuthUser;
use Thaliak\Models\User;
use Thaliak\Models\World;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();

        Route::model('auth', OAuthUser::class);

        Route::bind('character', function ($id) {
            return Character::with('profile')->findOrFail($id);
        });

        Route::bind('user', function ($user) {
            if ($user === 'me') {
                return request()->user();
            }

            return User::findOrFail($user);
        });

        Route::bind('user_by_name', function ($name) {
            return User::whereName(urldecode($name))->firstOrFail();
        });
    }

    public function map()
    {
        Route::group(['middleware' => ['throttle:60,1', 'bindings']], function ($router) {
            require base_path('routes.php');
        });
    }
}
