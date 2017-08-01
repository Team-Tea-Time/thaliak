<?php

namespace Thaliak\HTTP\Controllers;

use Illuminate\Http\Request;
use Thaliak\Models\CharacterProfile;

class CharacterProfilesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function save(Request $request): CharacterProfile
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

    public function deletePortrait(Request $request): CharacterProfile
    {
        $profile = $request->character->profile;

        if ($profile->portrait) {
            $profile->media->where('name', 'profile_portrait')->first()->delete();
        }

        return $profile->fresh();
    }
}
