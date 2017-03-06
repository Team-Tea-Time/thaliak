<?php

use Illuminate\Routing\Router;

// World context
Route::group([
    'domain'    => '{world}.' . config('app.domain'),
    'namespace' => 'World'
], function (Router $r) {
    // Current user
    $r->group(['prefix' => 'user'], function (Router $r) {
        $r->post('search', 'UserController@search');
        $r->patch('{user}', 'UserController@update');
        $r->delete('{user}', 'UserController@delete');
        $r->get('characters', 'UserController@characters');
    });

    // Users
    $r->group(['prefix' => 'users'], function (Router $r) {
        $r->get('/', 'UserController@index');
    });

    // Characters
    $r->group(['prefix' => 'character'], function (Router $r) {
        $r->get('/', 'CharacterController@index');
        $r->get('{character}', 'CharacterController@get');
        $r->post('search', 'CharacterController@search');
        $r->post('/', 'CharacterController@add');
        $r->post('{character}/verify', 'CharacterController@verify');
        $r->post('{character}/set-main', 'CharacterController@setMain');
        $r->patch('{character}', 'CharacterController@update');
        $r->delete('{character}', 'CharacterController@remove');
    });
});

// Current user
Route::group(['prefix' => 'user'], function (Router $r) {
    $r->get('/', 'UserController@get');
    $r->post('/', 'UserController@create');
    $r->post('verify', 'UserController@verify');
    $r->post('clear-token', 'UserController@clearToken');
});

// Parameter bindings
Route::bind('world', function ($world) {
    return \Thaliak\Models\World::whereName(ucfirst($world))->first();
});
