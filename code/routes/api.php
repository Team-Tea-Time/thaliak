<?php

use Illuminate\Routing\Router;

// World context
Route::group([
    'domain'    => '{world}.' . config('app.domain'),
    'namespace' => 'World'
], function (Router $r) {
    // Users
    $r->group(['prefix' => 'users'], function (Router $r) {
        $r->get('/', 'UsersController@index');
        $r->get('totals', 'UsersController@totals');
        $r->post('search', 'UsersController@search');
        $r->patch('{user}', 'UsersController@update');
        $r->delete('{user}', 'UsersController@delete');
        $r->get('{user}/characters', 'UsersController@characters');
    });

    // Characters
    $r->group(['prefix' => 'characters'], function (Router $r) {
        $r->get('/', 'CharactersController@index');
        $r->get('totals', 'CharactersController@totals');
        $r->get('{character}', 'CharactersController@get');
        $r->post('search', 'CharactersController@search');
        $r->post('/', 'CharactersController@add');
        $r->post('{character}/verify', 'CharactersController@verify');
        $r->post('{character}/set-main', 'CharactersController@setMain');
        $r->patch('{character}', 'CharactersController@update');
        $r->delete('{character}', 'CharactersController@remove');
    });
});

// Social auth
Route::get('social/drivers', 'SocialAuthController@drivers');
Route::group(['prefix' => 'social/{provider}/auth'], function (Router $r) {
    $r->get('/', 'SocialAuthController@redirect');
    $r->get('receive', 'SocialAuthController@receive');
});

// Current user
Route::group(['prefix' => 'user'], function (Router $r) {
    $r->get('me', 'UserController@get');
    $r->post('/', 'UserController@create');
    $r->post('verify', 'UserController@verify');
    $r->post('clear-token', 'UserController@clearToken');
});
