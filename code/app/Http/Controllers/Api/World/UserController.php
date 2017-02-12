<?php

namespace Thaliak\Http\Controllers\Api\World;

use Illuminate\Http\Request;
use Thaliak\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('api-auth');
    }

    /**
     * Get a list of the user's characters.
     *
     * @param  Request  $request
     * @return array
     */
    public function characters(Request $request)
    {
        return $request
            ->user()
            ->characters()
            ->world($request->route('world'))
            ->get();
    }
}
