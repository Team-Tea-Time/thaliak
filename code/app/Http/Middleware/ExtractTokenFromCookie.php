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
    /**
     * The encrypter implementation.
     *
     * @var \Illuminate\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Encryption\Encrypter  $encrypter
     * @return void
     */
    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cookie = $request->cookie(config('session.cookie'));

        if ($cookie) {
            $token = $this->encrypter->decrypt($cookie)['access_token'];
            $request->headers->set('Authorization', "Bearer {$token}");
        }

        return $next($request);
    }
}
