<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('user', UserController::class);
    $router->resource('pet', PetController::class);
    $router->get('tx/eth', 'TxController@getEthTxList');
    $router->get('tx/hlw', 'TxController@getHlwTxList');

});
