<?php

namespace Thaliak\Http\Controllers\Api\World;

use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Models\User;
use Thaliak\Support\User as UserSupport;

class UsersController extends Controller
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
     * Return an index of users.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        return User::paginate();
    }

    /**
     * Return user totals.
     *
     * @return array
     */
    public function totals()
    {
        return [
            'total' => User::count(),
            'unverified' => User::unverified()->count()
        ];
    }

    /**
     * Search for users by name.
     *
     * @param  Request  $request
     * @return \Illuminate\Support\Collection
     */
    public function search(Request $request)
    {
        $this->validate($request, ['name' => 'required|string']);
        return User::where('name', 'LIKE', "%{$request->name}%")->get();
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
        $user = User::byVerification($request->code)->firstOrFail();
        return $user->verify()->activate();
    }

    /**
     * Return a specified user.
     *
     * @param  Request  $request
     * @return User
     */
    public function get(Request $request)
    {
        return $request->user;
    }

    /**
     * Get a list of the current user's characters.
     *
     * @param  Request  $request
     * @return array
     */
    public function characters(Request $request)
    {
        return $request
            ->user
            ->characters()
            ->with('verification', 'profile')
            ->world($request->route('world'))
            ->get();
    }

    /**
     * Update a user.
     *
     * @param  Request  $request
     * @return User
     */
    public function update(Request $request)
    {
        $this->validate($request, ['password' => 'min:6|confirmed']);

        if (!Hash::check($request->current_password, $request->user->password)) {
            return response([
                'current_password' => ["Doesn't match current password"]
            ], 422);
        }

        $user = $request->user;

        if ($request->name && $request->name != $request->user->name) {
            $this->validate($request, ['name' => 'max:255|unique:users']);
            $user->name = $request->name;
        }

        if ($request->email && $request->email != $request->user->email) {
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
     * Update a user's state.
     *
     * @param  Request  $request
     * @return User
     */
    public function updateState(Request $request)
    {
        $this->validate($request, [
            'verified' => 'boolean',
            'active' => 'boolean'
        ]);

        $request->user->update($request->only(['verified', 'active']));

        return $request->user->fresh();
    }

    /**
     * Clear a user's auth token.
     *
     * @param  Request  $request
     * @return null
     */
    public function clearToken(Request $request)
    {
        Cookie::forget('auth');
        $request->user->token()->revoke();
    }

    /**
     * Delete a specified user.
     *
     * @param  Request  $request
     * @return User
     */
    public function delete(Request $request)
    {
        $request->user->delete();
        return $request->user;
    }
}
