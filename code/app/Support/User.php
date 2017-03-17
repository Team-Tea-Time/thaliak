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
    /**
     * Create a user.
     *
     * @param  array  $attributes
     * @return UserModel
     */
    public static function create($attributes)
    {
        $user = UserModel::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => bcrypt($attributes['password']),
            'verified' => 0,
            'active' => 0
        ]);
        $user->createVerificationCode();

        // Send the user a notification with the code
        $user->notify(new UserVerificationNotification);

        return $user;
    }

    /**
     * Create a user from a Socialite user.
     *
     * @param  AbstractUser  $socialite
     * @return UserModel
     */
    public static function createFromSocialite(AbstractUser $socialite)
    {
        return static::create([
            'name' => $socialite->name,
            'email' => $socialite->email,
            'password' => str_random(32)
        ]);
    }

    /**
     * Create an OAuth user from the given user, Socialite user and OAuth
     * driver.
     *
     * @param  UserModel  $user
     * @param  AbstractUser  $socialite
     * @param  OAuthDriver  $driver
     * @return OAuthUser
     */
    public static function createOAuthUser(
        UserModel $user,
        AbstractUser $socialite,
        OAuthDriver $driver
    )
    {
        return OAuthUser::create([
            'user_id' => $user->id,
            'provider_user_id' => $socialite->id,
            'access_token' => $socialite->token,
            'oauth_driver_id' => $driver->id
        ]);
    }
}
