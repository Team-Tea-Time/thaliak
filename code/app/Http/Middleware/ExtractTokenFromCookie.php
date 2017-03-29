<?php

namespace Thaliak\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ExtractTokenFromCookie
{
    protected $encrypter; // Encrypter

    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function handle(Request $request, Closure $next)
    {
        $cookie = $request->cookie(config('session.cookie'));

        if ($cookie) {
            $token = $this->encrypter->decrypt($cookie)['access_token'];
            $request->headers->set('Authorization', "Bearer {$token}");
        }

        return $next($request);
    }
}
