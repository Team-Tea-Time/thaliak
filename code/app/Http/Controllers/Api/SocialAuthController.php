<?php

namespace Thaliak\Http\Controllers\Api;

use Socialite;
use Thaliak\Http\Controllers\Controller;
use Thaliak\Models\OAuthDriver;
use Thaliak\Models\OAuthUser;
use Thaliak\Models\User;
use Thaliak\Support\Auth as AuthSupport;
use Thaliak\Support\User as UserSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class SocialAuthController extends Controller
{
    /**
     * @var OAuthDriver
     */
    private $driver;

    /**
     * @var \Laravel\Socialite\Two\AbstractProvider
     */
    private $provider;

    /**
     * @var AuthSupport
     */
    private $auth;

    /**
     * Create a new social auth controller instance.
     *
     * @param  Request  $request
     * @param  AuthSupport  $auth
     */
    public function __construct(Request $request, AuthSupport $auth)
    {
        if ($request->route('provider')) {
            $this->driver = $this->getDriver($request->route('provider'));
            $this->provider = $this->getProvider($request->route('provider'));
        }

        $this->auth = $auth;
    }

    /**
     * Return a list of available social OAuth drivers.
     *
     * @return array
     */
    public function drivers()
    {
        return OAuthDriver::whereActive(1)->get();
    }

    /**
     * Redirect the user to the given provider's authentication page.
     *
     * @param  string  $provider
     * @return Response
     */
    public function redirect(Request $request)
    {
        return new JsonResponse(
            $this->provider->redirect()->getTargetUrl()
        );
    }

    /**
     * Handle a provider callback.
     *
     * @param  Request  $request
     * @return Response
     */
    public function receive(Request $request)
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
                    'access_token' => $user->createToken("{$this->driver->name} access token")->accessToken
                ])
            );
        }

        return $response;
    }

    /**
     * Return a driver instance for the given provider.
     *
     * @param  string  $provider
     * @return OAuthDriver
     */
    private function getDriver($provider)
    {
        return OAuthDriver::whereName($provider)
                          ->whereActive(1)
                          ->firstOrFail();
    }

    /**
     * Return a Socialite provider instance for the given provider
     * name.
     *
     * @param  string  $provider
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    private function getProvider($provider)
    {
        return Socialite::driver($provider)->stateless();
    }
}
