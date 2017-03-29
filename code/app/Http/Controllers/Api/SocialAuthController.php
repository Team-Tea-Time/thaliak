<?php

namespace Thaliak\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Two\AbstractProvider;
use Socialite;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Models\OAuthDriver;
use Thaliak\Models\OAuthUser;
use Thaliak\Models\User;
use Thaliak\Support\Auth as AuthSupport;
use Thaliak\Support\User as UserSupport;
use Validator;

class SocialAuthController extends Controller
{
    private $driver;    // OAuthDriver
    private $provider;  // AbstractProvider
    private $auth;      // AuthSupport

    public function __construct(Request $request, AuthSupport $auth)
    {
        if ($request->route('provider')) {
            $this->driver = $this->getDriver($request->route('provider'));
            $this->provider = $this->getProvider($request->route('provider'));
        }

        $this->auth = $auth;
    }

    public function drivers(): Collection
    {
        return OAuthDriver::whereActive(1)->get();
    }

    public function redirect(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->provider->redirect()->getTargetUrl()
        );
    }

    public function receive(Request $request): JsonResponse
    {
        $auth = $this->provider->user();
        $state = [
            'user_is_new' => false,
            'user_has_new_auth' => false
        ];

        // Look up existing user by auth
        $user = User::forAuth($auth->id)->first();

        // If no user found, do a lookup via email address
        if (!$user) {
            $user = User::whereEmail($auth->email)->first();

            // If there's still no user, create one from the socialite details
            if (!$user) {
                $user = UserSupport::createFromSocialite($auth);
                $state['user_is_new'] = true;
            }

            // Create an OAuth user
            UserSupport::createOAuthUser($user, $auth, $this->driver);
            $state['user_has_new_auth'] = true;
        }

        $response = new JsonResponse(compact('user', 'state'));

        if (!$state['user_is_new'] && $user->active && $user->verified) {
            $response->headers->setCookie(
                $this->auth->createCookieForToken([
                    'access_token' => $user->createToken("{$this->driver->capitalised_name} login")->accessToken
                ])
            );
        }

        return $response;
    }

    public function delete(Request $request): OAuthUser
    {
        $request->auth->delete();
        return $request->auth;
    }

    private function getDriver($provider): OAuthDriver
    {
        return OAuthDriver::whereName($provider)
                          ->whereActive(1)
                          ->firstOrFail();
    }

    private function getProvider($provider): AbstractProvider
    {
        return Socialite::driver($provider)->stateless();
    }
}
