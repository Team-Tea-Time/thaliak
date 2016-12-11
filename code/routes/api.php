<?php

use Illuminate\Routing\Router;

// World context
Route::group([
    'domain'    => '{world}.' . config('app.domain'),
    'namespace' => 'World'
], function (Router $r) {
    // Current user
    $r->group(['prefix' => 'user'], function (Router $r) {
        $r->get('characters', 'UserController@characters');
    });

    // Characters
    $r->group(['prefix' => 'character'], function (Router $r) {
        $r->get('/', 'CharacterController@index');
        $r->post('search', 'CharacterController@search');
        $r->post('/', 'CharacterController@add');
        $r->post('verify', 'CharacterController@verify');
        $r->post('{character}/set-main', 'CharacterController@setMain');
        $r->delete('{character}', 'CharacterController@remove');
    });
});

// Current user
Route::group(['prefix' => 'user'], function (Router $r) {
    $r->get('/', 'UserController@get');
    $r->post('/', 'UserController@create');
    $r->post('verify', 'UserController@verify');
});

// Parameter bindings
Route::bind('world', function ($world) {
    return \Thaliak\Models\World::whereName(ucfirst($world))->first();
});
