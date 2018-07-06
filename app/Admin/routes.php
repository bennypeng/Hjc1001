<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'UserController@index');
    $router->resource('user', UserController::class);
    $router->resource('pet', PetController::class);
    $router->resource('eth', EthController::class);
    $router->resource('hlw', HlwController::class);
    $router->resource('ext', ExtractController::class);
    $router->get('match', 'MatchController@index');

});
