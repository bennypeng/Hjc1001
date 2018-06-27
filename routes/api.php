<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

/**
 * 需要登录权限的接口
 * 需要在header带上Authorization参数
 */
Route::middleware('jwt.auth')->group(function() {

    Route::prefix('user')->group(function() {

        Route::post('logout', 'UserController@logout');                  //  登出

        Route::post('nickname', 'UserController@changName');             //  修改昵称

        Route::post('icon', 'UserController@changIcon');                 //  修改头像

        Route::get('profile', 'UserController@profile');                 //  个人中心

        Route::post('binding', 'UserController@bindingAddress');         //  绑定钱包

        Route::post('extract', 'UserController@extractMoney');           //  发起提现请求

        Route::post('extopt', 'UserController@changeExtractStatus');     //  操作提现请求

        Route::get('record', 'UserController@getTxRecord');              //  查看交易记录

        Route::get('invite', 'UserController@getInviteInfo');            //  查看邀请码及代理信息

        //Route::post('agent', 'UserController@generateAgent');     //  生成代理身份

        //Route::get('qrcode/{id}', function ($address) {
        //    return view('QrCode')->with('address', $address);           //  生成二维码
        //});
    });

    //  宠物路由
    Route::prefix('pet')->group(function () {

        Route::post('/purchase', 'PetController@purchase');        //  宠物拍卖（购买）

        Route::post('/backout', 'PetController@backout');          //  宠物拍卖（下架）

        Route::post('/auction', 'PetController@auction');          //  宠物拍卖（上架）

        Route::post('/levelup', 'PetController@levelup');          //  宠物属性升级

        //Route::post('/okLevelup', 'PetController@oneKeylevelup');  //  宠物属性一键升级

    });

    //  比赛路由
    Route::prefix('match')->group(function () {

        Route::post('/join', 'MatchController@joinIn');             //  参加比赛

        Route::post('/vote', 'MatchController@vote');               //  比赛投票

    });

});

//  用户路由
Route::prefix('user')->group(function() {

    Route::post('login', 'UserController@login');             //  登录

    Route::post('register', 'UserController@regist');         //  注册

    Route::post('code', 'UserController@sendCode');           //  发送验证码

    Route::post('opt/{type}', 'UserController@sendOpt')
        ->where('type', '[1-2]');                      //  同意/拒绝提现请求

    Route::post('send', 'UserController@sendCoin');           //  下发eth或hlw

});

//  宠物路由
Route::prefix('pet')->group(function () {

    Route::get('/lists/{type}', 'PetController@getLists')
        ->where('type', '[1-2]');                       //  宠物列表

    Route::get('/birth', 'PetController@autoBirth');           //  宠物自动出生

    Route::get('/details/{id}', 'PetController@getDetails')
        ->where('id', '[0-9]+');                        //  获取宠物详情

});

//  比赛路由
Route::prefix('match')->group(function () {

    Route::get('/', 'MatchController@autoMatch');             //  生成一场比赛

    Route::get('/lists', 'MatchController@getLists');         //  获取比赛列表

    Route::get('/details/{matchType}/{sp}/{fp}', 'MatchController@getDetails')
        ->where('matchType', '[1-4]')
        ->where('sp', '[0-9]+')
        ->where('fp', '[0-9]+');                      //  获取比赛详情

    Route::get('/ranking/{matchType}/{sp}/{row}', 'MatchController@getRanking')
        ->where('matchType', '[1-4]')
        ->where('sp', '[0-9]+')
        ->where('row', '[0-9]+');                      //  获取排行榜信息

});

//  以太坊路由
Route::prefix('eth')->group(function () {

    Route::get('/sync/{action}/{address}', 'DownloadController@syncEthTransaction');                 //  同步以太坊交易记录

    Route::get('/txstatus/{txhash}', 'DownloadController@getEthTransactionStatus');                  //  获取订单状态

});

Route::get('/rebuild/mobile', function () {
    $mobiles = \Illuminate\Support\Facades\DB::select('SELECT mobile FROM Hjc1001.users');
    if ($mobiles) {
        foreach($mobiles as $v) {
            $key = 'M:' .  $v->mobile;
            \Illuminate\Support\Facades\Redis::select(5);
            \Illuminate\Support\Facades\Redis::set($key, 1);
        }
        return response('rebuild mobiles success');
    }
});

//  错误返回
Route::fallback(function (){
    return response()->json(['message' => 'Not Found!', 'code' => 404], 404);
});
