<?php

namespace Thaliak\Http\Middleware;

use Closure;
use Thaliak\Models\World;

class CORS
{
    public function handle($request, Closure $next)
    {
        $hostname = config('app.frontend_root_hostname');

        preg_match("/(?:\/\/)?([A-Za-z]*).{$hostname}/", $request->headers->get('origin'), $matches);
        $origin = !empty($matches[1]) && World::whereName($matches[1])->first()
            ? $request->headers->get('origin')
            : "http://{$hostname}";

        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        return $response;
    }
}
