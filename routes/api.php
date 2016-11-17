<?php

use Illuminate\Routing\Router;

Route::group(['prefix' => 'user'], function (Router $r) {
    $r->get('/', 'UserController@get');
    $r->post('/', 'UserController@create');
    $r->post('confirm', 'UserController@confirm');
});
