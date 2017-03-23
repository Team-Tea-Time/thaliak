<?php

namespace Thaliak\Http\Controllers\Api;

use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Notifications\UserVerification as UserVerificationNotification;
use Thaliak\Models\User;
use Thaliak\Models\UserVerification;
use Thaliak\Support\User as UserSupport;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('api-auth', ['except' => ['create', 'verify']]);
        $this->middleware('guest', ['only' => ['create', 'verify']]);
    }

    /**
     * Return the currently authenticated user.
     *
     * @param  Request  $request
     * @return User
     */
    public function get(Request $request)
    {
        return $request->user();
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

        $user = UserSupport::create(
            $request->only(['name', 'email', 'password'])
        );

        return $user->makeHidden('verification');
    }

    /**
     * Verify a user account via the given verification code.
     *
     * @param  Request  $request
     * @return User
     */
    public function verify(Request $request)
    {
        $user = User::byVerification($request->code)->first();

        if (!$user) {
            abort(404, 'User not found.');
        }

        return $user->verify()->activate();
    }

    /**
     * Update the current user.
     *
     * @param  Request  $request
     * @return User
     */
    public function update(Request $request)
    {
        $this->validate($request, ['password' => 'min:6|confirmed']);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response([
                'current_password' => ["Doesn't match current password"]
            ], 422);
        }

        $user = $request->user();

        if ($request->name && $request->name != $request->user()->name) {
            $this->validate($request, ['name' => 'max:255|unique:users']);
            $user->name = $request->name;
        }

        if ($request->email && $request->email != $request->user()->email) {
            $this->validate($request, ['email' => 'email|max:255|unique:users']);
            $user->email = $request->email;
            $user->verified = false;
            UserSupport::createVerificationCode($user);
        }

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return $user;
    }

    /**
     * Clear the current user's auth token.
     *
     * @param  Request  $request
     * @return null
     */
    public function clearToken(Request $request)
    {
        Cookie::forget('auth');
        $request->user()->token()->revoke();
    }
}
