<?php

namespace Thaliak\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('alpha_num_spaces', function ($attribute, $value) {
            return preg_match('/(^[A-Za-z0-9 ]+$)+/', $value);
        });
    }
}
