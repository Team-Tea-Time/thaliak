<?php

namespace Thaliak\Support;

use Thaliak\Notifications\UserVerification as UserVerificationNotification;
use Thaliak\Models\OAuthDriver;
use Thaliak\Models\OAuthUser;
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
            'verified' => 0,
            'active' => 0
        ]);

        $user->profile()->create([]);

        static::createVerificationCode($user);

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
            'password' => str_random(32)
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
}
