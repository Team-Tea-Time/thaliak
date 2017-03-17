<?php

namespace Thaliak\Support;

use Carbon\Carbon;
use Illuminate\Encryption\Encrypter;
use Laravel\Passport\PersonalAccessTokenResult as Token;
use Symfony\Component\HttpFoundation\Cookie;

class Auth
{
    /**
     * The encrypter implementation.
     *
     * @var \Illuminate\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Create an auth support instance.
     *
     * @param Encrypter
     */
    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Create a cookie for the given auth token.
     *
     * @param  array  $token
     * @return Cookie
     */
    public function createCookieForToken($token)
    {
        $config = config('session');

        $payload['access_token'] = $token['access_token'];

        if (isset($token['refresh_token'])) {
            $payload['refresh_token'] = $token['refresh_token'];
        }

        return new Cookie(
            $config['cookie'],
            $this->encrypter->encrypt($payload),
            Carbon::now()->addDays(config('auth.passport.expiration.access_token'))->toCookieString(),
            $config['path'],
            $config['domain'],
            $config['secure'],
            $config['http_only']
        );
    }
}
