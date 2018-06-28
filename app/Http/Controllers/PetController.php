<?php

namespace App\Http\Controllers;

use App\Contracts\HelperContract;
use App\User;
use App\Pet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class PetController extends Controller
{

    protected $helper;
    protected $petModel;
    protected $userModel;

    public function __construct(HelperContract $helper)
    {
        $this->helper     = $helper;
        $this->userModel  = new User;
        $this->petModel   = new Pet;
    }

    /**
     * 获取宠物信息
     * @param Request $req
     * @return JsonResponse
     */
    public function getDetails(Request $req) {
        $id = $req->route('id');

        //  缺少必填字段
        if (!$id) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  获取宠物信息
        $petInfo = $this->petModel->getPetDetails($id);

        //  没有找到该宠物
        if (!$petInfo) return response()->json(Config::get('constants.NOT_FOUND_PET'));

        return response()->json(
            array_merge(
                [
                    'detail' => $this->helper->parsePetDetails(array($petInfo), true)
                ],
                Config::get('constants.HANDLE_SUCCESS')
            )
        );
    }

    /**
     * 宠物自动出生
     * @return JsonResponse
     */
    public function autoBirth(Request $req) {

        //  请求不是来自服务器
        if (env('APP_IP') != $req->getClientIp())
            return response()->json(Config::get('constants.VERFY_IP_ERROR'));

        //  执行自动清理
        $this->petModel->delOutExpPets();

        //  出生冷却时间未达到
        if (time() < $this->helper->getCoolTime()) return response()->json(Config::get('constants.PETS_COOLTIME_ERROR'));

        //  出生数量已达上限
        if (Config::get('constants.PET_BIRTH_LIMIT') <= $this->petModel->getInExpPetsCounts())
            return response()->json(Config::get('constants.PETS_AMOUNT_ERROR'));

        //  随机抽取宠物
        $petType = $this->helper->generatePet();

        //  匹配数据失败
        if (!$petType) return response()->json(Config::get('constants.DATA_MATCHING_ERROR'));

        //  入库处理
        $petId = $this->petModel->createPet(
            array(
                'type'       => $petType,
                'expired_at' => Carbon::now()->addDay(),
                'on_sale'    => 2,
                'sp'         => Config::get('constants.PET_START_PRICE'),
                'fp'         => Config::get('constants.PET_FINAL_PRICE'),
                'attr4'      => rand(1, 5)
            )
        );

        //  入库失败
        if (!$petId) return response()->json(Config::get('constants.DATA_INSERT_ERROR'));

        //  更新冷却时间
        $this->helper->setCoolTime(time() + 15 * 60);

        Log::info('generate pet ' . $petId . ' success!');

        return response()->json(Config::get('constants.HANDLE_SUCCESS'));
    }

    /**
     * 宠物拍卖（上架）
     * @param Request $req
     * @return JsonResponse
     */
    public function auction(Request $req) {

        $petId    = $req->get('petId');
        $sp       = $req->get('sp');
        $fp       = $req->get('fp');

        //  缺少必填字段
        if (!$petId || !$sp || !$fp) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  缺少必填字段
        if ($sp < $fp || $fp <= 0 || $sp <= 0) return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));

        $petInfo = $this->petModel->getPetDetails($petId);

        //  未找到该宠物
        if (!$petInfo) return response()->json(Config::get('constants.NOT_FOUND_PET'));

        if (!$petInfo) return response()->json(Config::get('constants.NOT_FOUND_PET'));
        list($petDetails) = $this->helper->parsePetDetails([$petInfo]);

        //  宠物正在出售
        if ($petDetails['on_sale'] == 2 && time() < $petDetails['exp']) return response()->json(Config::get('constants.PETS_ON_SALE_ERROR'));

        //  获取当前宠物可参加的比赛类型
        $matchType = $this->helper->getMatchTypeByPetType($petInfo['type']);

        //  比赛类型错误
        if (!$matchType) return response()->json(Config::get('constants.MATCH_TYPE_ERROR'));

        //  宠物正在参加比赛
        if ($matchType && $petInfo['matchId'] == $this->helper->getMatchId($matchType)) return response()->json(Config::get('constants.PETS_ON_MATCH_ERROR'));

        //  更新操作
        $userInfo = Auth::guard('api')->user()->toArray();
        $userId   = $userInfo['id'];
        $update = [
            'sp' => $sp,
            'fp' => $fp,
            'on_sale' => 2,
            'expired_at' => Carbon::createFromTimestamp(time() + Config::get('constants.PET_SALE_EXP_SEC'))->toDateTimeString()
        ];
        if ($this->petModel->updatePet($userId, $petId, $update)) {
            Log::info('auction pet ' . $petId . ', sp ' . $sp . ', fp '. $fp . ' success!');
            return response()->json(Config::get('constants.HANDLE_SUCCESS'));
        }
        return response()->json(Config::get('constants.HANDLE_ERROR'));
    }

    /**
     * 宠物拍卖（下架）
     * @param Request $req
     * @return JsonResponse
     */
    public function backout(Request $req) {

        $petId    = $req->get('petId');

        //  缺少必填字段
        if (!$petId) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  未找到该宠物
        if (!$this->petModel->getPetDetails($petId)) return response()->json(Config::get('constants.NOT_FOUND_PET'));

        //  更新操作
        $userInfo = Auth::guard('api')->user()->toArray();
        $userId   = $userInfo['id'];
        $update = [
            'on_sale' => 1,
            'expired_at' => Carbon::now()
        ];
        if ($this->petModel->updatePet($userId, $petId, $update)) {
            Log::info('backout pet ' . $petId . ' success!');
            return response()->json(Config::get('constants.HANDLE_SUCCESS'));
        }
        return response()->json(Config::get('constants.HANDLE_ERROR'));
    }

    /**
     * 宠物拍卖（购买）
     * @param Request $req
     * @return JsonResponse
     */
    public function purchase(Request $req) {

        $petId    = $req->get('petId');

        //  缺少必填字段
        if (!$petId) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $petInfo  = $this->petModel->getPetDetails($petId);

        //  未找到该宠物
        if (!$petInfo) return response()->json(Config::get('constants.NOT_FOUND_PET'));

        list($petDetails) = $this->helper->parsePetDetails([$petInfo], true);
        $userInfo = Auth::guard('api')->user()->toArray();
        $userId   = $userInfo['id'];
        $wallet   = $userInfo['eth_wallet'];

        //  主人和购买者相同
        if ($petDetails['ownerId'] == $userId) return response()->json(Config::get('constants.PETS_OWNER_BUY_ERROR'));

        //  宠物未上架
        if ($petDetails['exp'] <= time()) return response()->json(Config::get('constants.PETS_OUT_EXP_ERROR'));

        //  余额不足
        if ($petDetails['price'] > $wallet) return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

        $update = [
            'ownerId' => $userId,
            'on_sale' => 1,
            'price' => $petDetails['price'],
            'expired_at' => Carbon::now()
        ];
        if ($this->petModel->updatePet($petDetails['ownerId'], $petId, $update)) {
            $this->userModel->updateUser($userId, ['eth_wallet' => $wallet - $petDetails['price']]);
            Log::info('purchase pet ' . $petId . ', from' . $petDetails['ownerId'] . ' to ' . $userId . ' success!');
            return response()->json(Config::get('constants.HANDLE_SUCCESS'));
        }
        return response()->json(Config::get('constants.HANDLE_ERROR'));
    }

    /**
     * 宠物属性升级
     * @param Request $req
     * @return JsonResponse
     */
    public function levelup(Request $req) {
        $petId    = $req->get('petId');
        $attr     = $req->get('attr');
        $idx      = $req->get('idx', 0);
        $update   = [];
        $cost     = 0;

        //  缺少必填字段
        if (!$petId || !$attr) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $petInfo  = $this->petModel->getPetDetails($petId);

        //  未找到该宠物
        if (!$petInfo) return response()->json(Config::get('constants.NOT_FOUND_PET'));

        list($petDetails) = $this->helper->parsePetDetails([$petInfo], true);
        $userInfo = Auth::guard('api')->user()->toArray();
        $userId   = $userInfo['id'];
        $wallet   = $userInfo['hlw_wallet'];

        //  不是宠物的主人
        if ($userId != $petDetails['ownerId']) return response()->json(Config::get('constants.PETS_OWNER_ERROR'));

        //  升级属性
        switch ($attr) {
            case 1:
            case 2:
                $flag = $attr == 1 ? 'strength' : 'attribute';

                //  属性达到上限
                if ($petDetails[$flag]['maxLevel'] <= $petDetails[$flag]['current']['level'])
                    return response()->json(Config::get('constants.PETS_ATTR_MAX_ERROR'));
                //  余额不足
                if ($wallet < $petDetails[$flag]['next']['cost'])
                    return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

                //  变更属性
                $nextLevel = $petDetails[$flag]['next']['level'];
                $update    = ['attr' . $attr => $nextLevel];
                $cost      = $petDetails[$flag]['next']['cost'];
                break;
            case 3:
                $binArr = $this->helper->parseBools2Nums($petDetails['decoration']);

                //  参数验证错误
                if (!isset($binArr[$idx])) return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));

                //  重复升级
                if ($binArr[$idx] == 1) return response()->json(Config::get('constants.PETS_ATTR_RE_ERROR'));

                //  余额不足
                if ($wallet < Config::get('constants.PETS_DECORATION_COST'))
                    return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

                //  更改属性
                $binArr[$idx] = 1;
                $cost         = Config::get('constants.PETS_DECORATION_COST');
                $update       = ['attr3' => bindec(implode("", $binArr))];
                break;
            default:
                return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));
        }

        if ($this->petModel->updatePet($userId, $petId, $update)) {
            //  花钱
            $this->userModel->updateUser($userId, ['hlw_wallet' => $wallet - $cost]);
            Log::info('levelup pet ' . $petId . ' success! update ', $update);
            return response()->json(Config::get('constants.HANDLE_SUCCESS'));
        }
        return response()->json(Config::get('constants.HANDLE_ERROR'));
    }

    /**
     * 获取宠物列表
     * @param Request $req
     * @return JsonResponse
     */
    public function getLists(Request $req) {
        $type = $req->route('type');   //  列表类型| 1诞生列表，2拍卖列表

        //  缺少必填字段
        if (!$type) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  获取宠物列表
        $petLists = $this->petModel->getPetLists($type);

        //  对宠物数据进行解析
        $petPraseLists = $this->helper->parsePetDetails($petLists);

        return response()->json(
            array_merge(
                [
                    'lists' => $petPraseLists
                ],
                Config::get('constants.HANDLE_SUCCESS')
            )
        );
    }

}
