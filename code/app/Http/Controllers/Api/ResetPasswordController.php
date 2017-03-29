<?php

namespace Thaliak\Http\Controllers\Api;

use Thaliak\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/'; // String

    public function __construct()
    {
        $this->middleware('guest');
    }
}
