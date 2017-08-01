<?php

namespace Thaliak\HTTP\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Thaliak\HTTP\Lodestone\API;
use Thaliak\Models\Character;
use Thaliak\Models\World;

class CharactersController extends Controller
{
    private $lodestone;

    public function __construct(API $lodestone)
    {
        $this->middleware('auth:api', ['except' => ['index', 'get']]);
        $this->lodestone = $lodestone;
    }

    public function index(Request $request): LengthAwarePaginator
    {
        return Character::paginate();
    }

    public function totals(): Array
    {
        return [
            'total' => Character::count(),
            'unverified' => Character::unverified()->count()
        ];
    }

    public function get(Request $request): Character
    {
        return $request->character;
    }

    public function add(Request $request): Character
    {
        $this->validate($request,
            [
                'id' => 'required|numeric|unique:characters',
                'world_id' => 'required|exists:worlds,id'
            ],
            [
                'id.unique' => 'This character has already been added.'
            ]
        );

        $lodestone = $this->lodestone->getCharacter($request->id);

        if (!$lodestone) {
            return response(['id' => 'Character not found.'], 422);
        }

        $character = Character::createFromLodestone(
            $lodestone,
            $request->user(),
            World::find($request->world_id)
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

    public function verify(Request $request)
    {
        $lodestone = $this->lodestone->getCharacter($request->character->id);

        if (!str_contains($lodestone->introduction, $request->code)) {
            return response(['code' => ['Verification failed. Please check the profile and try again.']], 422);
        }

        $request->character->profile()->create([]);

        return $request->character->verify();
    }

    public function setMain(Request $request): Character
    {
        return $request->character->setMain();
    }

    public function update(Request $request): Character
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

    public function delete(Request $request): Character
    {
        $request->character->delete();
        return $request->character;
    }
}
