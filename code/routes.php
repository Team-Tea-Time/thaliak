<?php

use Illuminate\Routing\Router;

// Thaliak API
Route::group(['namespace' => 'Thaliak\HTTP\Controllers'], function (Router $r) {
    // Lodestone
    $r->group(['prefix' => 'lodestone'], function (Router $r) {
        $r->group(['prefix' => 'characters'], function (Router $r) {
            $r->group(['prefix' => '{id}'], function (Router $r) {
                $r->get('/', 'LodestoneController@getCharacter');
            });
            $r->post('search', 'LodestoneController@findCharacters');
        });
        $r->group(['prefix' => 'freecompanies'], function (Router $r) {
            $r->group(['prefix' => '{id}'], function (Router $r) {
                $r->get('/', 'LodestoneController@getFreeCompany');
                $r->get('members', 'LodestoneController@getFreeCompanyMembers');
            });
            $r->post('search', 'LodestoneController@findFreeCompanies');
        });
    });

    // Data Centres
    $r->group(['prefix' => 'data-centres'], function (Router $r) {
        $r->get('/', 'DataCentresController@index');
    });

    // Worlds
    $r->group(['prefix' => 'worlds'], function (Router $r) {
        $r->get('/', 'WorldsController@index');
    });

    // Users
    $r->group(['prefix' => 'users'], function (Router $r) {
        $r->get('/', 'UsersController@index');
        $r->get('totals', 'UsersController@totals');
        $r->post('search', 'UsersController@search');
        $r->post('/', 'UsersController@create');
        $r->post('verify', 'UsersController@verify');

        $r->get('by-name/{user_by_name}', 'UsersController@getByName');

        $r->group(['prefix' => '{user}'], function (Router $r) {
            $r->get('/', 'UsersController@get');
            $r->get('characters', 'UsersController@characters');
            $r->patch('/', 'UsersController@update');
            $r->patch('state', 'UsersController@updateState');
            $r->post('clear-token', 'UsersController@clearToken');
            $r->delete('/', 'UsersController@delete');

            $r->group(['prefix' => 'profile'], function (Router $r) {
                $r->post('/', 'UserProfilesController@save');
                $r->delete('avatar', 'UserProfilesController@deleteAvatar');
            });
        });
    });

    // Characters
    $r->group(['prefix' => 'characters'], function (Router $r) {
        $r->get('/', 'CharactersController@index');
        $r->get('totals', 'CharactersController@totals');
        $r->post('search', 'CharactersController@search');
        $r->post('/', 'CharactersController@add');

        $r->group(['prefix' => '{character}'], function (Router $r) {
            $r->get('/', 'CharactersController@get');
            $r->post('verify', 'CharactersController@verify');
            $r->post('set-main', 'CharactersController@setMain');
            $r->patch('/', 'CharactersController@update');
            $r->delete('/', 'CharactersController@delete');

            $r->group(['prefix' => 'profile'], function (Router $r) {
                $r->post('/', 'CharacterProfilesController@save');
                $r->delete('portrait', 'CharacterProfilesController@deletePortrait');
            });
        });
    });

    // Social auth
    $r->get('social/drivers', 'SocialAuthController@drivers');
    $r->group(['prefix' => 'social/{provider}/auth'], function (Router $r) {
        $r->get('/', 'SocialAuthController@redirect');
        $r->get('receive', 'SocialAuthController@receive');
        $r->delete('{auth}', 'SocialAuthController@delete');
    });

    // Password resetting
    $r->group(['prefix' => 'auth/password'], function (Router $r) {
        $r->post('reset/request', 'PasswordResetController@request');
        $r->post('reset', 'PasswordResetController@reset');
    });
});

// Authentication (Passport)
Route::group([
    'prefix' => 'auth',
    'namespace' => 'Laravel\Passport\Http\Controllers'
], function (Router $r) {
    $r->group(['prefix' => 'token'], function (Router $r) {
        $r->post('/', [
            'middleware' => ['handle-grant-injections'],
            'uses' => 'AccessTokenController@issueToken'
        ]);
        $r->post('refresh', [
            'middleware' => ['handle-grant-injections'],
            'uses' => 'TransientTokenController@refresh'
        ]);
    });
});
