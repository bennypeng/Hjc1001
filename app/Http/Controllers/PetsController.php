<?php

namespace App\Http\Controllers;

use App\Contracts\HelperContract;
use App\Pets;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class PetsController extends Controller
{

    protected $helper;
    protected $petModel;
    protected $petOptions;

    public function __construct(HelperContract $helper)
    {
        $this->helper = $helper;
        $this->petModel = new Pets;
        $this->petOptions = Config::getMany(
            array(
                'constants.PETS_OPTIONS',
                'constants.PETS_STRENGTH_OPTIONS',
                'constants.PETS_ATTRIBUTE_OPTIONS')
        );
    }

    /**
     * 获取宠物信息
     * @param Request $req
     * @return JsonResponse
     */
    public function getDetails(Request $req) {
        return "";
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
        if (time() < $this->_getCoolTime()) return response()->json(Config::get('constants.PETS_COOLTIME_ERROR'));

        //  出生数量已达上限
        if (Config::get('constants.PET_BIRTH_LIMIT') <= $this->_getAmount())
            return response()->json(Config::get('constants.PETS_AMOUNT_ERROR'));

        //  随机抽取宠物
        $petType = $this->_generatePet();

        //  匹配数据失败
        if (!$petType) return response()->json(Config::get('constants.DATA_MATCHING_ERROR'));

        //  入库处理
        $petId = $this->petModel->createPet(
            array(
                'type'       => $petType,
                'expired_at' => Carbon::now()->addDay(),
                'on_sale'    => 2,
                'price'      => 0.15
            )
        );

        //  入库失败
        if (!$petId) return response()->json(Config::get('constants.DATA_INSERT_ERROR'));

        //  更新冷却时间及出生数量
        $this->_setCoolTime(time() + 15 * 60);
        $this->_setAmount(1);

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
        return "";
    }

    /**
     * 获取宠物列表
     * @param Request $req
     * @return JsonResponse
     */
    public function getLists(Request $req) {
        $type = $req->route('type');   //  列表类型| 1诞生列表，2拍卖列表

        //  缺少必填字段
        if (!$type) return response()->json(Config::get('constants.EMPTY_ERROR'));

        //  获取宠物列表
        if (false) {
            /**
             * @todo 缓存查询
             */
        } else {
            $petLists = $this->petModel->getPetLists($type);
        }

        //  空的列表
        if (empty($petLists)) return response()->json(Config::get('constants.LIST_EMPTY'));

        //  对宠物数据进行解析
        $petPraseLists = $this->_parsePetDetails($petLists);

        return response()->json(
            array_merge(
                Config::get('constants.HANDLE_SUCCESS'),
                array(
                    'lists' => $petPraseLists
                )
            )
        );

    }

    private function _getCoolTime() {
        $key = $this->_getCoolTimeKey();
        Redis::select(Config::get('constants.PETS_INDEX'));
        $ts = Redis::get($key);
        if (is_null($ts)) {
            $ts = time();
            $this->_setCoolTime($ts);
        }
        return $ts;
    }
    private function _setCoolTime($ts = '') {
        $key = $this->_getCoolTimeKey();
        Redis::select(Config::get('constants.PETS_INDEX'));
        Redis::set($key, $ts);
    }
    private function _getCoolTimeKey() {
        return 'PET:COOLTIME';
    }
    private function _getAmount() {
        $key = $this->_getAmountKey();
        Redis::select(Config::get('constants.PETS_INDEX'));
        $num = Redis::get($key);
        if (is_null($num)) {
            $num = 0;
            $this->_setAmount($num);
        }
        return $num;
    }
    private function _setAmount($num = 0) {
        $key = $this->_getAmountKey();
        Redis::select(Config::get('constants.PETS_INDEX'));
        Redis::incrby($key, $num);
    }
    private function _getAmountKey() {
        return 'PET:AMOUNT';
    }
    private function _generatePet() {
        $allowList = array();
        $publishTs      = strtotime(Config::get('constants.PUBLISH_TIME'));
        $ts             = time();

        foreach($this->petOptions['constants.PETS_OPTIONS'] as $k => $v) {
            if ($ts < $v[0] * 86400 + $publishTs) continue;
            $allowList[$k] = $v[1];
        }
        if (empty($allowList)) return false;

        //  随机获取一个宠物
        return $this->helper->getRandomByWeight($allowList);
    }
    private function _parsePetDetails(array $data) {
        $res = array();
        foreach($data as $k => $v) {
            $res[$v['id']] = array(
                'id' => $v['id'],
                'petType' => $v['type'],
                'price' => $v['price'],
                'rarity' => $this->_calcRarity($v)
            );
        }
        krsort($res);
        return array_values($res);
    }
    private function _calcRarity(array $petInfo) {
        //稀有值=[角色体力值(体力*成长系数)+属性值（萌力/形体/肌肉）]*装饰完整度
        return round((
            $this->petOptions['constants.PETS_STRENGTH_OPTIONS'][$petInfo['attr1']][0]
            * $this->petOptions['constants.PETS_OPTIONS'][$petInfo['id']][2]
            + $this->petOptions['constants.PETS_ATTRIBUTE_OPTIONS'][$petInfo['attr2']][0]
        ) * $petInfo['attr3']);

    }
}
