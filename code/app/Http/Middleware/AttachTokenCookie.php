<?php

namespace Thaliak\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Thaliak\Support\Auth;

class AttachTokenCookie
{
    /**
     * @var Auth
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  Auth  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
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
        $response = $next($request);

        if ($this->containsValidGrantType($request) && $response->isOk()) {
            $response->headers->setCookie($this->make($response));
        }

        return $response;
    }

    /**
     * Create a new API token cookie.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function make(Response $response)
    {
        $token = json_decode($response->getContent(), true);
        return $this->auth->createCookieForToken($token);
    }

    /**
     * Determine if the request contains a valid grant type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function containsValidGrantType(Request $request)
    {
        return in_array($request->grant_type, ['password', 'refresh_token']);
    }
}
