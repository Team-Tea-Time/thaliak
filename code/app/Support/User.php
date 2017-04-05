<?php

namespace Thaliak\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Thaliak\Notifications\PasswordReset as PasswordResetNotification;
use Thaliak\Notifications\UserVerification as UserVerificationNotification;
use Thaliak\Models\OAuthDriver;
use Thaliak\Models\OAuthUser;
use Thaliak\Models\PasswordReset;
use Thaliak\Models\User as UserModel;
use Thaliak\Models\UserVerification;
use Laravel\Socialite\AbstractUser;

class User
{
    public static function create(Array $attributes): UserModel
    {
        $user = UserModel::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => bcrypt($attributes['password']),
            'verified' => $attributes['verified'] ? 1 : 0,
            'active' => $attributes['active'] ? 1 : 0
        ]);

        $user->profile()->create([]);

        if (!$attributes['verified']) {
            static::createVerificationCode($user);
        }

        return $user;
    }

    public static function createVerificationCode(UserModel $user)
    {
        $user->createVerificationCode();
        $user->notify(new UserVerificationNotification);
    }

    public static function createFromSocialite(AbstractUser $socialite): UserModel
    {
        return static::create([
            'name' => $socialite->name,
            'email' => $socialite->email,
            'password' => Hash::make(Str::random(32))
        ]);
    }

    public static function createOAuthUser(
        UserModel $user,
        AbstractUser $socialite,
        OAuthDriver $driver
    ): OAuthUser
    {
        return OAuthUser::create([
            'user_id' => $user->id,
            'provider_user_id' => $socialite->id,
            'access_token' => $socialite->token,
            'oauth_driver_id' => $driver->id
        ]);
    }

    public static function issuePasswordReset(String $email): PasswordReset
    {
        $user = UserModel::whereEmail($email)->first();

        $token = Str::random(16);

        if ($user->passwordReset) {
            $user->passwordReset->delete();
        }

        $user->passwordReset()->create([
            'token' => Hash::make($token)
        ]);

        $user->notify(new PasswordResetNotification($token));

        return $user->fresh()->passwordReset;
    }

    public static function resetPassword(
        String $token,
        String $email,
        String $password
    ): UserModel
    {
        $passwordReset = PasswordReset::whereEmail($email)->first();

        if (!Hash::check($token, $passwordReset->token)) {
            abort(422, ['token' => ["Token is invalid."]]);
        }

        $user = $passwordReset->user;

        $user->update(['password' => Hash::make($password)]);
        $passwordReset->delete();

        return $user;
    }
}
