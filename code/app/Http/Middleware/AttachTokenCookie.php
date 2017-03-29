<?php

namespace Thaliak\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Thaliak\Support\Auth;

class AttachTokenCookie
{
    protected $auth; // Auth

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->containsValidGrantType($request) && $response->isOk()) {
            $response->headers->setCookie($this->make($response));
        }

        return $response;
    }

    protected function make(Response $response): Cookie
    {
        $token = json_decode($response->getContent(), true);
        return $this->auth->createCookieForToken($token);
    }

    protected function containsValidGrantType(Request $request): Bool
    {
        return in_array($request->grant_type, ['password', 'refresh_token']);
    }
}
