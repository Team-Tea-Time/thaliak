<?php

namespace Thaliak\Http\Controllers\Api;

use Illuminate\Http\Request;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Notifications\UserConfirmation as UserConfirmationNotification;
use Thaliak\User;
use Thaliak\UserConfirmation;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create', 'confirm']]);
        $this->middleware('guest', ['only' => ['create', 'confirm']]);
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

        // Create the user, along with a confirmation code
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'confirmed' => 0,
            'active' => 0
        ]);
        $user->confirmation()->create(['code' => str_random(16)]);

        // Send the user a notification with the code
        $user->notify(new UserConfirmationNotification);

        return $user;
    }

    /**
     * Confirm a user account via the given confirmation code.
     *
     * @param  Request  $request
     * @return User
     */
    public function confirm(Request $request)
    {
        $user = User::findForConfirmation($request->code);

        if (!$user) {
            abort(404, 'User not found.');
        }

        return $user->confirm()->activate();
    }
}
