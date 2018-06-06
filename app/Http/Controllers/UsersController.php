<?php

namespace App\Http\Controllers;

use App\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class UsersController extends Controller
{

    public function __construct() {}

    /**
     * 用户登录
     * @param Request $req
     * @return JsonResponse
     */
    public function login(Request $req) {

        $mobile    = $req->get('mobile');
        $pwd       = $req->get('pwd');

        //  缺少必填字段
        if (!$mobile || !$pwd) return response()->json(Config::get('constants.EMPTY_USER_OR_PWD'));

        if (Redis::exists($mobile)) {
            $res = Redis::hgetall($mobile);
        } else {
            $userModel = new Users;
            $res = $userModel->checkUserExist($mobile);

            if ($res && is_object($res)) {
                $res = $res->toArray();
                Redis::hmset($mobile, $res);
            } else {
                //  找不到该用户
                return response()->json(Config::get('constants.NOT_FOUND_USER'));
            }
        }

        //  密码错误
        if ($res['pwd'] != substr(md5($pwd), 8, 16))
            return response()->json(Config::get('constants.LOGIN_ERROR'));

        unset($res['id'], $res['pwd']);

        //  保存session
        $req->session()->put($mobile, 1);
        $req->session()->save();


        //  登录成功
        return response()->json(array_merge($res, Config::get('constants.LOGIN_SUCCESS')));
    }

    /**
     * 注册账号
     * @param Request $req
     * @return JsonResponse
     */
    public function register(Request $req) {
        $mobile    = $req->get('mobile');
        $verfyCode = $req->get('verfyCode');
        $pwd       = $req->get('pwd');

        //  缺少必填字段
        if (!$mobile || !$verfyCode || !$pwd) return response()->json(Config::get('constants.EMPTY_ERROR'));

        //  账号已被注册
        if (Redis::exists($mobile)) return response()->json(Config::get('constants.ALREADY_EXIST_USER'));

        //  校验验证码
        if ($verfyCode != "111") return response()->json(Config::get('constants.VERFY_CODE_ERROR'));

        $userModel = new Users;
        if ($userModel->registerUser($mobile, $pwd)) {
            Redis::del($mobile);
            $res = $userModel->checkUserExist($mobile);
            if ($res && is_object($res)) {
                $res = $res->toArray();
                Redis::hmset($mobile, $res);
                return response()->json(Config::get('constants.REGIST_SUCCESS'));
            }
            return response()->json(Config::get('constants.REGIST_ERROR'));
        }
        return response()->json(Config::get('constants.REGIST_ERROR'));
    }

    /**
     * 修改昵称
     * @param Request $req
     * @return JsonResponse
     */
    public function changName(Request $req) {
        $mobile      = $req->get('mobile');
        $nickname    = $req->get('nickname');

        //  缺少必填字段
        if (!$mobile || !$nickname) return response()->json(Config::get('constants.EMPTY_ERROR'));

        $userModel = new Users;
        $res = $userModel->updateUser($mobile, ['nickname' => $nickname]);
        if ($res) return response()->json(Config::get('constants.UPDATE_SUCCESS'));
        return response()->json(Config::get('constants.UPDATE_ERROR'));
    }

    /**
     * 获取登录状态
     * @param Request $req
     * @return JsonResponse
     */
    public function loginStatus(Request $req) {
        $mobile = $req->get('mobile');

        //  缺少必填字段
        if (!$mobile) return response()->json(Config::get('constants.EMPTY_ERROR'));

        if ($req->session()->has($mobile))
            return response()->json(Config::get('constants.LOGIN_SUCCESS'));

        return response()->json(Config::get('constants.LOGIN_HACK'));
    }


}
