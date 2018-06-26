<?php

namespace App\Http\Controllers;

use App\Contracts\HelperContract;
use App\User;
use App\Pet;
use App\Trascation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    protected $helper;
    protected $userModel;
    protected $petModel;
    protected $txModel;

    public function __construct(HelperContract $helper) {
        $this->helper    = $helper;
        $this->userModel = new User;
        $this->petModel  = new Pet;
        $this->txModel   = new Trascation;
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
        if (!$mobile || !$verfyCode || !$password) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  手机号已被注册
        if ($this->helper->checkMobileExist($mobile)) return response()->json(Config::get('constants.ALREADY_EXIST_MOBILE'));

        //  校验验证码
        if ($verfyCode != $this->helper->getVerfyCode($mobile)) return response()->json(Config::get('constants.VERFY_CODE_ERROR'));

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
            Log::info($mobile . ' regist success');
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
        $password  = $req->get('password');

        //  手机号还没注册
        if (!$this->helper->checkMobileExist($mobile)) return response()->json(Config::get('constants.NOT_FOUND_USER'));

        //  登录成功
        if ($token = Auth::guard('api')->attempt(['mobile' => $mobile, 'password' => $password])) {
            Log::info($mobile . ' login success');
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
        if (!$nickname) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $userInfo = Auth::guard('api')->user()->toArray();

        $res = $this->userModel->updateUser($userInfo['id'], ['nickname' => $nickname]);

        //  修改昵称失败
        if (!$res) return response()->json(Config::get('constants.UPDATE_ERROR'));

        Log::info('user ' . $userInfo['id'] . ' change nickname ' . $nickname . ' success');

        return response()->json(Config::get('constants.UPDATE_SUCCESS'));

    }

    /**
     * 修改头像
     * @param Request $req
     * @return JsonResponse
     */
    public function changIcon(Request $req) {
        $icon    = $req->get('icon');

        //  缺少必填字段
        if (!$icon) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $userInfo = Auth::guard('api')->user()->toArray();

        $res = $this->userModel->updateUser($userInfo['id'], ['icon' => $icon]);

        //  修改头像失败
        if (!$res) return response()->json(Config::get('constants.UPDATE_ERROR'));

        Log::info('user ' . $userInfo['id'] . ' change icon ' . $icon . ' success');

        return response()->json(Config::get('constants.UPDATE_SUCCESS'));

    }

    /**
     * 绑定地址
     * @param Request $req
     * @return JsonResponse
     */
    public function bindingAddress(Request $req) {
        $addr    = $req->get('addr');

        //  缺少必填字段
        if (!$addr) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $userInfo = Auth::guard('api')->user()->toArray();

        if ($userInfo['address']) return response()->json(Config::get('constants.BIND_REPEAT_ERROR'));

        $res = $this->userModel->updateUser($userInfo['id'], ['address' => $addr]);

        //  绑定钱包失败
        if (!$res) return response()->json(Config::get('constants.HANDLE_ERROR'));

        Log::info('user ' . $userInfo['id'] . ' binding address ' . $addr . ' success');

        return response()->json(Config::get('constants.HANDLE_SUCCESS'));

    }

    /**
     * 提现操作
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function extractMoney(Request $req) {
        $money    = $req->get('money');
        $type     = $req->get('type');

        if ($type == 1)
            $money = floor($money);

        //  缺少必填字段
        if (!$money || !$type) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  提现类型错误
        if (!in_array($type, [1, 2])) return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));

        $userInfo = Auth::guard('api')->user()->toArray();

        //  未绑定钱包
        if (!$userInfo['address']) return response()->json(Config::get('constants.WALLET_NOT_BIND_ERROR'));

        $wallet = $type == 1 ? $userInfo['hlw_wallet'] : $userInfo['eth_wallet'];

        //  未达到提现要求
        if (($type == 1 && $wallet < 2000) || ($type == 2 && $wallet < 1))
            return response()->json(Config::get('constants.WALLET_REQ_EXTRA_ERROR'));

        //  数据错误
        if ($money < 0 || ($type == 1 && $money < 1))
            return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));

        //  提现超出余额
        if ($money > $wallet) return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

        //  修改用户余额
        if ($type == 1) {
            $update = ['hlw_wallet' => $wallet - $money, 'hlw_lock_wallet' => $userInfo['hlw_lock_wallet'] + $money];
        } else {
            $update = ['eth_wallet' => $wallet - $money, 'eth_lock_wallet' => $userInfo['eth_lock_wallet'] + $money];
        }
        $res = $this->userModel->updateUser($userInfo['id'], $update);

        //  修改失败
        if (!$res) return response()->json(Config::get('constants.HANDLE_ERROR'));

        //  写入提现列表
        $flag = $type == 1 ? 'hlw' : 'eth';
        $this->helper->setExtractList($userInfo['address'], ['money' => $money, 'flag' => $flag, 'status' => 0, 'time' => time()]);

        return response()->json(Config::get('constants.HANDLE_SUCCESS'));
    }

    public function changeExtractStatus(Request $req) {
        $id        = $req->get('id');
        $status    = $req->get('status');

        //  状态类型错误
        if (!in_array($status, [-1, 1])) return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));

        //  请求不是来自服务器（允许提现只能有后台进行操作）
        if ($status == 1) {
            if (Config::get('constants.SERVER_IP') != $req->getClientIp())
                return response()->json(Config::get('constants.VERFY_IP_ERROR'));
        }

        //  缺少必填字段
        if (!$id || !$status) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $userInfo = Auth::guard('api')->user()->toArray();

        //  验证token错误
        if (!$userInfo) return response()->json(Config::get('constants.VERFY_TOKEN_ERROR'));

        //  未绑定钱包
        if (!$userInfo['address']) return response()->json(Config::get('constants.WALLET_NOT_BIND_ERROR'));

        //  单号不存在
        if (!$this->helper->checkExtractExist($userInfo['address'], $id))
            return response()->json(Config::get('constants.EXTRA_NOT_FOUND_ERROR'));

        $this->helper->setExtractStatus($userInfo['address'], $id, $status);

        return response()->json(Config::get('constants.HANDLE_SUCCESS'));
    }

    /**
     * 交易记录
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTxRecord(Request $req) {

        $userInfo = Auth::guard('api')->user()->toArray();

        //  验证token错误
        if (!$userInfo) return response()->json(Config::get('constants.VERFY_TOKEN_ERROR'));

        $res = array(
            'myAddr' => $userInfo['address'],
            'rechargeAddr' => Config::get('constants.ETH_ADDR'),
            'extLists' => [],
            'txLists' => [
                'out' => [], 'in' => [],
            ],
        );

        //  未绑定钱包
        if (!$userInfo['address']) return response()->json(array_merge(['records' => $res], Config::get('constants.HANDLE_SUCCESS')));

        $res['extLists'] = $this->helper->getExtractList($userInfo['address']);
        $lists = $this->txModel->getTrascationsByAddress($userInfo['address']);
        foreach($lists as $v) {
            $info = [
                'tx'    => $v['hash'],
                'money' => $v['tokenSymbol'] ? round($v['value'] / 10000, 4) : round($v['value'] / 1000000000000000000, 4),
                'time'  => $v['timeStamp'],
                'flag'  => $v['tokenSymbol'] ? $v['tokenSymbol'] : 'ETH'
            ];
            if ($v['from'] == $userInfo['address']) {
                $res['txLists']['out'][] = $info;
            } else {
                $res['txLists']['in'][] = $info;
            }
        }

        return response()->json(array_merge(
                [
                    'records'   => $res
                ],
                Config::get('constants.HANDLE_SUCCESS'))
        );
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
            $now = time();
            $petInfoLists = $this->helper->parsePetDetails($userPetLists);
            foreach($petInfoLists as $k => $v) {
                if ($v['on_sale'] == 2 && $now < $v['exp']) {
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

    /**
     * 发送验证码
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendCode(Request $req) {

        $mobile    = $req->get('mobile');

        //  缺少必填字段
        if (!$mobile) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  手机号已被注册
        if ($this->helper->checkMobileExist($mobile)) return response()->json(Config::get('constants.ALREADY_EXIST_MOBILE'));

        $randomCode = $this->helper->getVerfyCode($mobile);
        if ($randomCode)
            return response()->json(array_merge(
                ['verfyCode' => $randomCode],
                Config::get('constants.HANDLE_SUCCESS')
            ));

        //  验证码发送次数上限
        if ($this->helper->getVerfyCodeLimit($req->getClientIp()) >= Config::get('constants.VERFY_CODE_LIMIT'))
            return response()->json(Config::get('constants.VERFY_CODE_LIMIT_ERROR'));

        //  发送验证码
        $randomCode = $this->helper->reqVerfyCode($mobile);
        $this->helper->setVerfyCodeLimit($req->getClientIp());

        Log::info('ip ' . $req->getClientIp() . ' request random code success');

        if (!$randomCode)
            return response()->json(array_merge(
                ['verfyCode' => ''],
                Config::get('constants.HANDLE_ERROR')
            ));

        return response()->json(array_merge(
            ['verfyCode' => $randomCode],
            Config::get('constants.HANDLE_SUCCESS')
        ));

    }

}
