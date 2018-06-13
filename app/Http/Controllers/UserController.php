<?php

namespace App\Http\Controllers;

use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{

    protected $userModel;

    public function __construct() {
        $this->userModel = new User;
    }

    /**
     * 注册账号
     * @param Request $req
     * @return JsonResponse
     */
    public function regist(Request $req) {

        $mobile    = $req->get('mobile');
        $verfyCode = $req->get('verfyCode');
        $password  = $req->get('password');

        //  缺少必填字段
        if (!$mobile || !$verfyCode || !$password) return response()->json(Config::get('constants.EMPTY_ERROR'));

        //  手机号已被注册
        //if ($this->_checkMobileExist($mobile)) return response()->json(Config::get('constants.ALREADY_EXIST_MOBILE'));

        //  校验验证码
        if ($verfyCode != "111") return response()->json(Config::get('constants.VERFY_CODE_ERROR'));

        //  注册用户

        $userId = $this->userModel->registerUser(
            array(
                'mobile' => $mobile,
                'nickname' => $mobile,
                'password' => bcrypt($password)
            )
        );

        //  注册失败
        if (!$userId) return response()->json(Config::get('constants.REGIST_ERROR'));

        //  获取用户信息，并写入缓存
        $res = $this->userModel->getUserByUserId($userId);
        if ($res) {
            $this->_setUserInfo($userId, $res);
            return response()->json(Config::get('constants.REGIST_SUCCESS'));
        }
        return response()->json(Config::get('constants.DATA_MATCHING_ERROR'));
    }

    /**
     * 用户登录
     * @param Request $req
     * @return JsonResponse
     */
    public function login(Request $req) {

        $rules = [
            'mobile'   => [
                'required',
                'exists:users',
            ],
            'password' => 'required|string|min:6|max:20',
        ];

        $params = $this->validate($req, $rules);

        //  登录成功
        if ($token = Auth::guard('api')->attempt($params)) {

            $userInfo = Auth::guard('api')->user()->toArray();

            return response()->json(array_merge(
                ['token' => 'bearer ' . $token, 'userInfo' => $userInfo],
                Config::get('constants.LOGIN_SUCCESS'))
            );
        } else {
            return response()->json(Config::get('constants.LOGIN_ERROR'));
        }
    }

    /**
     * 用户登出
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(Config::get('constants.LOGIN_OUT'));
    }

    /**
     * 修改昵称
     * @param Request $req
     * @return JsonResponse
     */
    public function changName(Request $req) {
        $nickname    = $req->get('nickname');

        //  缺少必填字段
        if (!$nickname) return response()->json(Config::get('constants.EMPTY_ERROR'));

        $userInfo = Auth::guard('api')->user()->toArray();

        $res = $this->userModel->updateUser($userInfo['id'], ['nickname' => $nickname]);
        if ($res) {
            $this->_delUserKey($userInfo['id']);
            $this->_setUserInfo($userInfo['id'], $userInfo);
            return response()->json(Config::get('constants.UPDATE_SUCCESS'));
        }
        return response()->json(Config::get('constants.UPDATE_ERROR'));
    }

    private function _setUserInfo($userId = '', $data = []) {
        $key = $this->_getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        Redis::hmset($key, $data);
    }
    private function _delUserKey($userId = '') {
        $key = $this->_getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        Redis::del($key);
    }
    private function _getUserInfoByUserId($userId = '') {
        $key = $this->_getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        return Redis::hgetall($key);
    }
    private function _checkUserExist($userId = '') {
        $key = $this->_getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        return Redis::exists($key);
    }
    private function _getUserKey($userId = '') {
        return 'U:' . $userId;
    }

}
