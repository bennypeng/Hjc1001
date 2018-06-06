<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//  日志查询
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

//  用户路由
Route::prefix('users')->group(function () {

    Route::post('/login', 'UsersController@login');

    Route::post('/register', 'UsersController@register');

    Route::post('/nickname', 'UsersController@changName');

    Route::post('/status/', 'UsersController@loginStatus');

    Route::get('/qrcode/{id}', function ($address) {
        return view('QrCode')->with('address', $address);
    });

});

//  错误返回
Route::fallback(function (){
    return response()->json(['message' => 'Not Found!', 'code' => 404], 404);
});