<?php

namespace Thaliak\Http\Controllers;

use Illuminate\Http\Request;
use Thaliak\Models\UserProfile;

class UserProfilesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function save(Request $request): UserProfile
    {
        $this->validate($request, ['body' => 'string']);

        $profile = $request->user->profile;

        $profile->update($request->only('body'));

        if ($request->hasFile('avatar')) {
            $this->validate($request, [
                'portrait' => 'mimetypes:image/jpg,image/jpeg,image/png|max:250'
            ]);

            if ($profile->avatar) {
                $profile->media->where('name', 'avatar')->first()->delete();
            }

            $profile
                ->addMediaFromUrl($request->avatar)
                ->usingFileName('avatar')
                ->usingName('avatar')
                ->toMediaLibrary('images');
        }

        return $profile->fresh();
    }

    public function deleteAvatar(Request $request): UserProfile
    {
        $profile = $request->user->profile;

        if ($profile->avatar) {
            $profile->media->where('name', 'avatar')->first()->delete();
        }

        return $profile->fresh();
    }
}
