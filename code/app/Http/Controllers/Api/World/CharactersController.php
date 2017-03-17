<?php

namespace Thaliak\Http\Controllers\Api\World;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Http\Lodestone\Api;
use Thaliak\Models\Character;
use Thaliak\Models\World;

class CharactersController extends Controller
{
    /**
     * @var Api
     */
    private $lodestone;

    /**
     * Create a new controller instance.
     *
     * @param  Api  $lodestone
     * @return void
     */
    public function __construct(Api $lodestone)
    {
        $this->middleware('api-auth');
        $this->lodestone = $lodestone;
    }

    /**
     * Return an index of characters.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        return Character::paginate();
    }

    /**
     * Return character totals.
     *
     * @return array
     */
    public function totals()
    {
        return [
            'total' => Character::count(),
            'unverified' => Character::unverified()->count()
        ];
    }

    /**
     * Return a specified character.
     *
     * @param  Request  $request
     * @return Character
     */
    public function get(Request $request)
    {
        return $request->character;
    }

    /**
     * Search for characters on Lodestone by name.
     *
     * @param  Request  $request
     * @return \Illuminate\Support\Collection
     */
    public function search(Request $request)
    {
        $this->validate($request, ['name' => 'required|string']);

        return $this->lodestone->searchCharacter(
            $request->name,
            $request->route('world')->name
        );
    }

    /**
     * Add a character from Lodestone by ID.
     *
     * @param  Request  $request
     * @return Character
     */
    public function add(Request $request)
    {
        $this->validate($request,
            ['id' => 'required|numeric|unique:characters'],
            ['id.unique' => 'This character has already been added.']
        );

        $lodestone = $this->lodestone->getCharacter($request->id);

        if (!$lodestone) {
            return response(['id' => 'Character not found.'], 422);
        }

        $character = Character::createFromLodestone(
            $lodestone,
            $request->user(),
            $request->route('world')
        );
        $character->createVerificationCode();

        $character
            ->addMediaFromUrl($lodestone->avatar)
            ->usingName('avatar')
            ->toMediaLibrary('images');

        $character
            ->addMediaFromUrl($lodestone->portrait)
            ->usingName('portrait')
            ->toMediaLibrary('images');

        return $character->load('verification');
    }

    /**
     * Verify character ownership via Lodestone.
     *
     * @param  Request  $request
     * @return Character
     */
    public function verify(Request $request)
    {
        $lodestone = $this->lodestone->getCharacter($request->character->id);

        if (!str_contains($lodestone->introduction, $request->code)) {
            return response(['code' => ['Verification failed. Please check the profile and try again.']], 422);
        }

        return $request->character->verify();
    }

    /**
     * Set a character as 'main'.
     *
     * @param  Request  $request
     * @return Character
     */
    public function setMain(Request $request)
    {
        return $request->character->setMain();
    }

    /**
     * Update a character.
     *
     * @param  Request  $request
     * @return Character
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'verified' => 'boolean',
            'user_id' => 'sometimes|exists:users,id'
        ]);

        $request->character->update([
            'verified' => $request->verified,
            'user_id' => $request->user_id ? $request->user_id : $request->character->user_id
        ]);

        return $request->character->fresh();
    }

    /**
     * Remove a character.
     *
     * @param  Request  $request
     * @return Character
     */
    public function remove(Request $request)
    {
        $request->character->delete();
        return $request->character;
    }
}
