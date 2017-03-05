<?php

namespace Thaliak\Http\Controllers\Api\World;

use Illuminate\Http\Request;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Models\User;

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
     * Update a user.
     *
     * @param  Request  $request
     * @return User
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'verified' => 'boolean',
            'active' => 'boolean'
        ]);

        $request->user->update($request->only(['verified', 'active']));

        return $request->user->fresh();
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

    /**
     * Get a list of the current user's characters.
     *
     * @param  Request  $request
     * @return array
     */
    public function characters(Request $request)
    {
        return $request
            ->user()
            ->characters()
            ->with('verification')
            ->world($request->route('world'))
            ->get();
    }
}
