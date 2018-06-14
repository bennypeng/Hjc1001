<?php

namespace App\Http\Controllers;

use App\Contracts\HelperContract;
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

    public function __construct(HelperContract $helper)
    {
        $this->helper     = $helper;
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
        if (Config::get('constants.SERVER_IP') != $req->getClientIp())
            return response()->json(Config::get('constants.VERFY_IP_ERROR'));

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
                'price'      => 0.15,
                'attr4'      => rand(1, 10)
            )
        );

        //  入库失败
        if (!$petId) return response()->json(Config::get('constants.DATA_INSERT_ERROR'));

        //  更新冷却时间
        $this->helper->setCoolTime(time() + 15 * 60);

        Log::info("debug-" . __FUNCTION__, array('message' => "SUCCESS GENERATE THE ". $this->helper->getAmount() . "th PET!"));

        return response()->json(Config::get('constants.HANDLE_SUCCESS'));
    }

    /**
     * 宠物拍卖
     * @param Request $req
     * @return JsonResponse
     */
    public function auction(Request $req) {
        return "";
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

        //  缺少必填字段
        if (!$petId || !$attr) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $petInfo  = $this->petModel->getPetDetails($petId);

        //  未找到该宠物
        if (!$petInfo) return response()->json(Config::get('constants.NOT_FOUND_PET'));

        list($petDetails) = $this->helper->parsePetDetails([$petInfo], true);
        $userInfo = Auth::guard('api')->user()->toArray();
        $userId   = $userInfo['id'];
        $wallet   = $userInfo['wallet'];

        //  不是宠物的主人
        if ($userId != $petDetails['ownerId']) return response()->json(Config::get('constants.PETS_OWNER_ERROR'));

        //  升级属性
        switch ($attr) {
            case 1:
                //  属性1
                //  属性达到上限
                if ($petDetails['strength']['maxLevel'] <= $petDetails['strength']['current']['level'])
                    return response()->json(Config::get('constants.PETS_ATTR_MAX_ERROR'));

                //  余额不足
                //if ($wallet < $petDetails['strength']['next']['cost'])
                //    return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

                //$ret = $this->petModel->updatePet($userId, $petId, []);
                ;
                break;
            case 2:
                //  属性2
                //  属性达到上限
                if ($petDetails['attribute']['maxLevel'] <= $petDetails['attribute']['current']['level'])
                    return response()->json(Config::get('constants.PETS_ATTR_MAX_ERROR'));

                //  余额不足
                //if ($wallet < $petDetails['attribute']['next']['cost'])
                //    return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

                //$ret = $this->petModel->updatePet($userId, $petId, []);
                ;
                break;
            case 3:
                //  属性3
                if ($petDetails['attribute']['maxLevel'] <= $petDetails['attribute']['current']['level'])
                    return response()->json(Config::get('constants.PETS_ATTR_MAX_ERROR'));

                //  余额不足
                //if ($wallet < $petDetails['attribute']['next']['cost'])
                //    return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

                //$ret = $this->petModel->updatePet($userId, $petId, []);
                ;
                break;
            default:
                return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));
        }

        //$res = $this->petModel->updatePet($userInfo['id'], $petId, []);

        //  升级属性失败
        //if (!$res) return response()->json(Config::get('constants.HANDLE_ERROR'));

        //  删除宠物缓存信息
        //$this->helper->delPetInfo($petId);

        //return response()->json(Config::get('constants.PETS_ATTR_ERROR'));

      //  return "";
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
        if ($petLists) {

            $petPraseLists = $this->helper->parsePetDetails($petLists);

            return response()->json(
                array_merge(
                    [
                        'lists' => $petPraseLists
                    ],
                    Config::get('constants.HANDLE_SUCCESS')
                )
            );
        } else {
            return response()->json(Config::get('constants.HANDLE_SUCCESS'));
        }


    }

}
