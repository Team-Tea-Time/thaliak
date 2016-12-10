<?php

use Illuminate\Routing\Router;

// Current user
Route::group(['prefix' => 'user'], function (Router $r) {
    $r->get('/', 'UserController@get');
    $r->post('/', 'UserController@create');
    $r->post('confirm', 'UserController@confirm');
});

// World context
Route::group([
    'domain'    => '{world}.' . config('app.domain'),
    'namespace' => 'World'
], function (Router $r) {
    // Current user
    $r->group(['prefix' => 'user'], function (Router $r) {
        $r->get('characters', 'UserController@characters');
    });
});
