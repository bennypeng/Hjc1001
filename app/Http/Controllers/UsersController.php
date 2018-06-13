<?php

namespace App\Http\Controllers;

use App\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class UsersController extends Controller
{

    protected $userModel;

    public function __construct() {
        $this->userModel = new Users;
    }

    /**
     * 注册账号
     * @param Request $req
     * @return JsonResponse
     */
    public function regist(Request $req) {
        $mobile    = $req->get('mobile');
        $verfyCode = $req->get('verfyCode');
        $pwd       = $req->get('pwd');

        //  缺少必填字段
        if (!$mobile || !$verfyCode || !$pwd) return response()->json(Config::get('constants.EMPTY_ERROR'));

        //  手机号已被注册
        if ($this->_checkMobileExist($mobile)) return response()->json(Config::get('constants.ALREADY_EXIST_MOBILE'));

        //  校验验证码
        if ($verfyCode != "111") return response()->json(Config::get('constants.VERFY_CODE_ERROR'));

        //  注册用户

        $userId = $this->userModel->registerUser(
            array(
                'mobile' => $mobile,
                'nickname' => $mobile,
                'pwd' => substr(md5($pwd), 8, 16)
            )
        );

        //  注册失败
        if (!$userId) return response()->json(Config::get('constants.REGIST_ERROR'));

        //  获取用户信息，并写入缓存
        $res = $this->userModel->getUserByUserId($userId);
        if ($res) {
            $this->_setUserInfo($userId, $res);
            $this->_setMobile($mobile, $res);
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

        $mobile    = $req->get('mobile');
        $pwd       = $req->get('pwd');

        //  缺少必填字段
        if (!$mobile || !$pwd) return response()->json(Config::get('constants.EMPTY_ERROR'));

        //  获取用户信息
        if ($this->_checkMobileExist($mobile)) {
            $res = $this->_getUserInfoByMobile($mobile);
        } else {
            $res = $this->userModel->getUserByMobile($mobile);

            //  找不到该用户
            if (!$res) return response()->json(Config::get('constants.NOT_FOUND_USER'));

            $this->_setUserInfo($res['id'], $res);
            $this->_setMobile($mobile, $res);
        }

        //  密码错误
        if ($res['pwd'] != substr(md5($pwd), 8, 16))
            return response()->json(Config::get('constants.LOGIN_ERROR'));

        unset($res['pwd']);

        //  保存登陆状态
        $this->_setLoginSatus($res['id']);

        //  登录成功
        return response()->json(array_merge($res, Config::get('constants.LOGIN_SUCCESS')));
    }

    /**
     * 修改昵称
     * @param Request $req
     * @return JsonResponse
     */
    public function changName(Request $req) {
        $userId      = $req->get('uid');
        $nickname    = $req->get('nickname');

        //  缺少必填字段
        if (!$userId || !$nickname) return response()->json(Config::get('constants.EMPTY_ERROR'));

        $res = $this->userModel->updateUser($userId, ['nickname' => $nickname]);
        if ($res) {
            $this->_delUserKey($userId);
            return response()->json(Config::get('constants.UPDATE_SUCCESS'));
        }
        return response()->json(Config::get('constants.UPDATE_ERROR'));
    }

    /**
     * 获取登录状态
     * @param Request $req
     * @return JsonResponse
     */
    public function getLoginStatus(Request $req) {
        $userId = $req->get('uid');

        //  缺少必填字段
        if (!$userId) return response()->json(Config::get('constants.EMPTY_ERROR'));

        if ($this->_getLoginStatus($userId))
            return response()->json(Config::get('constants.LOGIN_SUCCESS'));

        return response()->json(Config::get('constants.LOGIN_HACK'));
    }


    private function _setLoginSatus($userId = '') {
        $key = $this->_getLoginSatusKey($userId);
        Redis::select(Config::get('constants.LOGIN_INDEX'));
        Redis::set($key, 1);
        Redis::expire($key, 7200);
    }
    private function _getLoginStatus($userId = '') {
        $key = $this->_getUserKey($userId);
        Redis::select(Config::get('constants.LOGIN_INDEX'));
        return Redis::exists($key);
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
    private function _getUserInfoByMobile($mobile = '') {
        $key = $this->_getMobileKey($mobile);
        Redis::select(Config::get('constants.USERS_INDEX'));
        return Redis::hgetall($key);
    }
    private function _checkUserExist($userId = '') {
        $key = $this->_getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        return Redis::exists($key);
    }
    private function _checkMobileExist($mobile = '') {
        $key = $this->_getMobileKey($mobile);
        Redis::select(Config::get('constants.USERS_INDEX'));
        return Redis::exists($key);
    }
    private function _setMobile($mobile = '', $data = []) {
        $key = $this->_getUserKey($mobile);
        Redis::select(Config::get('constants.USERS_INDEX'));
        Redis::hmset($key, $data);
    }
    private function _getUserKey($userId = '') {
        return 'U:' . $userId;
    }
    private function _getMobileKey($mobile = '') {
        return 'M:' . $mobile;
    }
    private function _getLoginStatusKey() {}
}
