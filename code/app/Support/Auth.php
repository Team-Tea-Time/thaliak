<?php

namespace Thaliak\Support;

use Carbon\Carbon;
use Illuminate\Encryption\Encrypter;
use Laravel\Passport\PersonalAccessTokenResult as Token;
use Symfony\Component\HttpFoundation\Cookie;

class Auth
{
    protected $encrypter; // Encrypter

    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function createCookieForToken(Array $token): Cookie
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
