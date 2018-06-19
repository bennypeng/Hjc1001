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

    /**
     * 自动生成一场比赛
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoMatch() {

        //  请求不是来自服务器
        if (Config::get('constants.SERVER_IP') != $req->getClientIp())
            return response()->json(Config::get('constants.VERFY_IP_ERROR'));

        $matchOptions = Config::get('constants.MATCHES_OPTIONS');

        //  没有找到比赛
        if (!$matchOptions) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        $macthesInfo = $this->helper->parseMatchOptions($matchOptions, true);

        $curTs = time();
        foreach($macthesInfo['lists'] as $k => $v) {
            //  如果没有设置冷却值，先给它设置;
            //  如果当前时间大于冷却时间，则再开一场
            $coolTime = $this->helper->getMatchCoolTime($v['matchType']);
            if (!$coolTime || $curTs > $coolTime) {
                //  每周两场， 需要判断当前该开那一场
                foreach($v['openTime'] as $idx => $val) {
                    if (!($curTs > $val[0] && $curTs < $val[1]))
                        continue;
                    $coolTime = $val[1];
                }
                $this->helper->setMatchCoolTime($v['matchType'], !isset($coolTime) ? time() : $coolTime);

                //  生成新的比赛ID
                $matchId = $this->helper->setMatchId($v['matchType']);

                //  设置往期比赛ID
                $this->helper->setMatchHisIds($v['matchType'], $matchId);
            }
        }
        return response()->json(Config::get('constants.HANDLE_SUCCESS'));
    }

    /**
     * 获取比赛列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLists() {

        $matchOptions = Config::get('constants.MATCHES_OPTIONS');

        //  没有找到比赛
        if (!$matchOptions) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        return response()->json(
            array_merge(
                [
                    'macthesInfo' => $this->helper->parseMatchOptions($matchOptions, true),
                ],
                Config::get('constants.HANDLE_SUCCESS')
            )
        );
    }

    /**
     * 获取比赛详情
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Request $req) {
        $matchType = $req->route('matchType');

        //  缺少必填字段
        if (!$matchType) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  获取当前比赛ID
        $matchId = $this->helper->getMatchId($matchType);

        //  没有找到该比赛
        if (!$matchId) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        //  获取排行榜信息
        $ranking = $this->helper->getMatchRanking($matchType, $matchId, 0, 99);

        /**
         * @todo 需要根据获取的排行榜列表，获取用户的头像及昵称信息
         */

        $residueSec = $this->helper->getMatchCoolTime($matchType) - time();

        $residueSec = $residueSec > 0 ? $residueSec : -1;

        return response()->json(
            array_merge(
                [
                    'residueSec' => $residueSec,
                    'ranking'    => $ranking
                ],
                Config::get('constants.HANDLE_SUCCESS')
            )
        );

    }

    public function joinIn(Request $req) {

    }
}
