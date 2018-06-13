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

    Route::post('/login', 'UsersController@login');             //  登录

    Route::post('/register', 'UsersController@regist');         //  注册

    Route::post('/nickname', 'UsersController@changName');      //  修改昵称

    Route::post('/status', 'UsersController@getLoginStatus');   //  查询登录状态

    Route::post('/agent', 'UsersController@generateAgent');     //  生成代理身份

    Route::get('/qrcode/{id}', function ($address) {
        return view('QrCode')->with('address', $address);             //  生成二维码
    });

});

//  宠物路由
Route::prefix('pets')->group(function () {

    Route::get('/lists/{type}', 'PetsController@getLists')
        ->where('type', '[1-2]');                        //  宠物列表

    Route::get('/birth', 'PetsController@autoBirth');           //  宠物自动出生

    Route::get('/details/{id}', 'PetsController@getDetails')
        ->where('type', '[0-9]+');                       //  获取宠物详情

    Route::post('/auction', 'PetsController@auction');          //  宠物拍卖

    Route::post('/levelup', 'PetsController@levelup');          //  宠物属性升级

});

//  比赛路由
Route::prefix('match')->group(function () {

    Route::post('/', 'MatchesController@generateMatch');        //  生成一场比赛

    Route::post('/status', 'MatchesController@checkStatus');    //  获取比赛状态

    Route::post('/vote', 'MatchesController@vote');             //  比赛投票

    Route::post('/ranking', 'MatchesController@getRanking');    //  获取排行榜信息


});

//  错误返回
Route::fallback(function (){
    return response()->json(['message' => 'Not Found!', 'code' => 404], 404);
});