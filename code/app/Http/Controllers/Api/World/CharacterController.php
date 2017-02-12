<?php

namespace Thaliak\Http\Controllers\Api\World;

use Illuminate\Http\Request;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Http\Lodestone\Api;
use Thaliak\Models\Character;
use Thaliak\Models\World;

class CharacterController extends Controller
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

        $lodestoneCharacter = $this->lodestone->getCharacter($request->id);

        if (!$lodestoneCharacter) {
            return response(['id' => 'Character not found.'], 422);
        }

        $character = Character::createFromLodestone(
            $lodestoneCharacter,
            $request->user(),
            $request->route('world')
        );
        $character->createVerificationCode();

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

        return $character->verify();
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
