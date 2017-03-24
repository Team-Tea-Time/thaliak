<?php

namespace Thaliak\Http\Controllers\Api\World;

use Illuminate\Http\Request;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Models\CharacterProfile;

class CharacterProfilesController extends Controller
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
     * Save a character's profile, creating a new one if it doesn't exist.
     *
     * @param  Request  $request
     * @return CharacterProfile
     */
    public function save(Request $request)
    {
        $this->validate($request, ['body' => 'string']);

        $profile = !$request->character->profile
                 ? $request->character->profile()->create([])
                 : $request->character->profile;

        $profile->update($request->only('body'));

        if ($request->hasFile('portrait')) {
            $this->validate($request, [
                'portrait' => 'mimetypes:image/jpg,image/jpeg,image/png|max:800'
            ]);

            if ($profile->portrait) {
                $profile->media->where('name', 'profile_portrait')->first()->delete();
            }

            $profile
                ->addMediaFromUrl($request->portrait)
                ->usingFileName('portrait')
                ->usingName('profile_portrait')
                ->toMediaLibrary('images');
        }

        return $profile->fresh();
    }

    /**
     * Delete a character's profile portrait if it exists.
     *
     * @param  Request  $request
     * @return CharacterProfile
     */
    public function deletePortrait(Request $request)
    {
        $profile = $request->character->profile;

        if ($profile->portrait) {
            $profile->media->where('name', 'profile_portrait')->first()->delete();
        }

        return $profile->fresh();
    }
}
