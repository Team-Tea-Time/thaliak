<?php

namespace Thaliak\Http\Controllers\Oauth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Thaliak\Http\Controllers\Controller;

class ProxyController extends Controller
{
    /**
     * @var object
     */
    private $client;

    /**
     * Create a new Oauth Proxy Controller instance.
     */
    public function __construct()
    {
        $this->client = DB::table('oauth_clients')
            ->where('id', config('auth.password_grant_client_id'))
            ->first();
    }

    /**
     * Proxy method for Passport's oauth/token route.
     *
     * @param Request $request
     * @return mixed
     */
    protected function getToken(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|max:255',
            'password' => 'required|min:6'
        ]);

        $request->request->add([
            'grant_type' => 'password',
            'username' => $request->username,
            'password' => $request->password,
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'scope' => '*'
        ]);

        $proxy = Request::create(
            '/oauth/token',
            'POST'
        );

        return Route::dispatch($proxy);
    }

    /**
     * Proxy method for Passport's oauth/token/refresh route.
     *
     * @param Request $request
     * @return mixed
     */
    protected function refreshToken(Request $request)
    {
        $request->request->add([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
        ]);

        $proxy = Request::create(
            '/oauth/token/refresh',
            'POST'
        );

        return Route::dispatch($proxy);
    }
}
