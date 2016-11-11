<?php

namespace Thaliak\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Thaliak\User;
use Thaliak\UserConfirmation;
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
        $this->middleware('auth:api', ['except' => 'create']);
        $this->middleware('guest', ['only' => 'create']);
    }

    /**
     * Return the currently authenticated user.
     *
     * @param  Request  $request
     * @return User
     */
    public function get(Request $request)
    {
        return $request->user;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request  $request
     * @return User
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'confirmed' => 0,
            'active' => 0
        ]);

        UserConfirmation::create([
            'user_id' => $user->id,
            'code' => str_random(32)
        ]);

        return new JsonResponse($user);
    }
}
