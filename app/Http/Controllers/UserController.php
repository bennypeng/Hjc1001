<?php

namespace App\Http\Controllers;

use App\Contracts\HelperContract;
use App\User;
use App\Pet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class UserController extends Controller
{

    protected $helper;
    protected $userModel;
    protected $petModel;

    public function __construct(HelperContract $helper) {
        $this->helper    = $helper;
        $this->userModel = new User;
        $this->petModel  = new Pet;
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
        if ($this->helper->checkMobileExist($mobile)) return response()->json(Config::get('constants.ALREADY_EXIST_MOBILE'));

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
            $this->helper->setMobile($mobile);
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

            return response()->json(array_merge(
                    ['token' => 'bearer ' . $token],
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

        //  修改昵称失败
        if (!$res) return response()->json(Config::get('constants.UPDATE_ERROR'));

        return response()->json(Config::get('constants.UPDATE_SUCCESS'));

    }

    /**
     * 个人中心
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile() {

        $userInfo = Auth::guard('api')->user()->toArray();

        //  验证token错误
        if (!$userInfo) return response()->json(Config::get('constants.VERFY_TOKEN_ERROR'));

        $userPetLists = $this->petModel->getUserPetLists($userInfo['id']);

        $myAutionList = $myPetList = array();
        if ($userPetLists) {
            $petInfoLists = $this->helper->parsePetDetails($userPetLists);
            foreach($petInfoLists as $k => $v) {
                if ($v['on_sale'] == 2) {
                    $myAutionList[] = $v;
                } else {
                    $myPetList[] = $v;
                }
            }
        }
        return response()->json(array_merge(
            [
                'userInfo'   => $userInfo,
                'petList'    => $myPetList,
                'autionList' => $myAutionList
            ],
            Config::get('constants.HANDLE_SUCCESS'))
        );
    }

}
