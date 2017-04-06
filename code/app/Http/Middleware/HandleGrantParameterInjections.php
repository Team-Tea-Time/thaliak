<?php

namespace Thaliak\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class HandleGrantParameterInjections
{
    protected $clients;
    protected $encrypter;

    public function __construct(ClientRepository $clients, Encrypter $encrypter)
    {
        $this->clients = $clients;
        $this->encrypter = $encrypter;
    }

    public function handle(Request $request, Closure $next)
    {
        if ($request->grant_type === 'password') {
            $client = $this->clients->find($request->client_id);

            if ($client === null) {
                throw (new ModelNotFoundException)->setModel(Client::class);
            }

            $request->request->add([
                'client_secret' => $client->secret,
            ]);
        }

        if ($request->grant_type === 'refresh_token') {
            $payload = $this->encrypter->decrypt($request->cookie(config('session.cookie')));

            $request->request->add([
                'refresh_token' => $payload['refresh_token'],
            ]);
        }

        return $next($request);
    }
}
