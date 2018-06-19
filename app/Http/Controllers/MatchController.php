<?php

namespace App\Http\Controllers;

use App\Contracts\HelperContract;
use App\Match;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class MatchController extends Controller
{

    protected $helper;
    protected $matchModel;

    public function __construct(HelperContract $helper)
    {
        $this->helper       = $helper;
        $this->matchModel   = new Match;
    }

    public function autoMatch(Request $req) {

        //  请求不是来自服务器
        if (Config::get('constants.SERVER_IP') != $req->getClientIp())
            return response()->json(Config::get('constants.VERFY_IP_ERROR'));

        /*
        //  出生冷却时间未达到
        //if (time() < $this->helper->getCoolTime()) return response()->json(Config::get('constants.PETS_COOLTIME_ERROR'));

        //  出生数量已达上限
        //if (Config::get('constants.PET_BIRTH_LIMIT') <= $this->petModel->getInExpPetsCounts())
        //    return response()->json(Config::get('constants.PETS_AMOUNT_ERROR'));

        //  随机抽取宠物
        //$petType = $this->helper->generatePet();

        //  匹配数据失败
        //if (!$petType) return response()->json(Config::get('constants.DATA_MATCHING_ERROR'));

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

        return response()->json(Config::get('constants.HANDLE_SUCCESS'));
        */
    }

    /**
     * 获取比赛列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLists() {

        $matchOptions = Config::get('constants.MATCHES_OPTIONS');

        //  没有找到比赛
        if (!$matchOptions) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        $res = array();
        foreach ($matchOptions as $k => $v) {
            $res[] = [
                'matchType'  => $k,
                'allowTypes' => $v[0],
                'cost'       => $v[1]
            ];
        }
        return response()->json(
            array_merge(
                [
                    'macthesInfo' => [
                        'lists'   => $res,
                        'rewards' => Config::get('constants.MATCHES_REWARDS')
                    ],
                ],
                Config::get('constants.HANDLE_SUCCESS')
            )
        );
    }

    public function getDetails(Request $req) {
        $id = $req->route('id');

        //  缺少必填字段
        if (!$id) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  获取比赛信息
        $matchInfo = $this->matchModel->getMatchDetails($id);
/*
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
*/
    }
}
