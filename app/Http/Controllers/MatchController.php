<?php

namespace App\Http\Controllers;

use App\Contracts\HelperContract;
use App\Match;
use App\Pet;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MatchController extends Controller
{

    protected $helper;
    protected $matchModel;
    protected $petModel;
    protected $userModel;

    public function __construct(HelperContract $helper)
    {
        $this->helper       = $helper;
        $this->petModel     = new Pet;
        $this->matchModel   = new Match;
        $this->userModel    = new User;
    }

    /**
     * 自动生成一场比赛
     */
    public function autoMatch(Request $req) {

        //  请求不是来自服务器
        if (env('APP_IP') != $req->getClientIp())
            return response()->json(Config::get('constants.VERFY_IP_ERROR'));

        $matchOptions = Config::get('constants.MATCHES_OPTIONS');

        //  没有找到比赛
        if (!$matchOptions) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        $macthesInfo = $this->helper->parseMatchOptions($matchOptions, true);

        $newMatchIds = array();
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

                $newMatchIds[] = $matchId;
            }
        }

        if ($newMatchIds) {
            return response()->json(array_merge(
                ['matchIds' => $newMatchIds],
                Config::get('constants.HANDLE_SUCCESS')));
        }

    }

    /**
     * 获取比赛列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLists() {

        /**
         * @todo 临时关闭比赛
         */
        return response()->json(Config::get('constants.MATCH_OPEN_ERROR'));

        $matchOptions = Config::get('constants.MATCHES_OPTIONS');

        //  没有找到比赛
        if (!$matchOptions) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        return response()->json(
            array_merge(
                [
                    'matchesInfo' => $this->helper->parseMatchOptions($matchOptions, true),
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
        $sp        = $req->route('sp');
        $fp        = $req->route('fp');

        /**
         * @todo 临时关闭比赛
         */
        return response()->json(Config::get('constants.MATCH_OPEN_ERROR'));

        //  缺少必填字段
        if (!$matchType || !$sp || !$fp) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  获取当前比赛ID
        $matchId = $this->helper->getMatchId($matchType);

        //  没有找到该比赛
        if (!$matchId) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        //  获取排行榜信息
        $ranking = $this->helper->getMatchRanking($matchType, $matchId, $sp - 1, $fp - 1);

        $residueSec = $this->helper->getMatchCoolTime($matchType) - time();

        $residueSec = $residueSec > 0 ? $residueSec : -1;

        $periodsArr = $this->helper->getPeriods($matchType, [$matchId]);
        $period = isset($periodsArr[$matchId]['period']) ? $periodsArr[$matchId]['period'] : 1;
        $flag = isset($periodsArr[$matchId]['flag']) ? $periodsArr[$matchId]['flag'] : 1;

        return response()->json(
            array_merge(
                [
                    'period'     => $period,
                    'flag'       => $flag,
                    'total'      => $this->helper->getMatchRankingLen($matchType, $matchId),
                    'residueSec' => $residueSec,
                    'lists'      => $ranking
                ],
                Config::get('constants.HANDLE_SUCCESS')
            )
        );

    }

    /**
     * 参加比赛
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinIn(Request $req) {
        $petIds     = $req->get('petIds');
        $matchType  = $req->get('matchType');

        //  缺少必填字段
        if (!$petIds || !$matchType) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $matchOptions = Config::get('constants.MATCHES_OPTIONS');

        //  没有找到比赛
        if (!$matchOptions) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        $matchOptions = $this->helper->parseMatchOptions($matchOptions, true);

        $matchInfo = array();
        foreach($matchOptions['lists'] as $v) {
            if ($v['matchType'] != $matchType) continue;
            $matchInfo = $v;
            break;
        }

        //  输入的参数错误，未匹配到相应的比赛
        if (!$matchInfo) return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));

        $matchId  = $this->helper->getMatchId($matchType);
        $matchLen = $this->helper->getMatchRankingLen($matchType, $matchId);

        //  该比赛人数已达上限
        if ($matchLen >= $matchInfo['joinLimit']) return response()->json(Config::get('constants.MATCH_LEN_ERROR'));

        $userInfo  = Auth::guard('api')->user()->toArray();
        $userId    = $userInfo['id'];
        $wallet    = $userInfo['hlw_wallet'];
        $petIdsArr = explode(',', $petIds);
        $curTs     = time();
        $cost      = 0;

        foreach($petIdsArr as $v) {
            $petInfo  = $this->petModel->getPetDetails($v);
            //  未达到参赛条件
            if ($petInfo['attr1'] != 6 || $petInfo['attr2'] !=6 || $petInfo['attr3'] != 63)
                return response()->json(Config::get('constants.MATCH_JOIN_ERROR'));
            //  未找到该宠物
            if (!$petInfo) return response()->json(Config::get('constants.NOT_FOUND_PET'));
            list($petDetails) = $this->helper->parsePetDetails([$petInfo]);
            //  该宠物不能参加该类比赛
            if (!in_array($petDetails['petType'], $matchInfo['allowTypes'])) return response()->json(Config::get('constants.PETS_MATCH_TYPE_ERROR'));
            //  不是宠物的主人
            if ($userId != $petDetails['ownerId']) return response()->json(Config::get('constants.PETS_OWNER_ERROR'));
            //  宠物正在出售状态
            if ($petDetails['on_sale'] == 2 && $curTs < $petDetails['exp']) return response()->json(Config::get('constants.PETS_ON_SALE_ERROR'));
            //  宠物正在参加比赛中
            if ($petDetails['matchId'] == $matchId) return response()->json(Config::get('constants.PETS_ON_MATCH_ERROR'));
            $cost += $matchInfo['cost'];
        }

        $balance = $wallet - $cost;

        //  余额不足
        if ($balance < 0) return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

        //  参赛
        foreach($petIdsArr as $pet) {
            if ($this->petModel->updatePet($userId, $pet, ['matchId' => $matchId])) {
                $this->userModel->updateUser($userId, ['hlw_wallet' => $balance]);
                $this->helper->setMatchRanking($matchType, $matchId, $pet, 0);
            } else {
                return response()->json(Config::get('constants.HANDLE_ERROR'));
            }
        }
        Log::info('join match userId ' . $userId . ', matchType ' . $matchType . ', matchId' . $matchId . ', petIds ', is_array($petIds) ? $petIds : []);
        return response()->json(Config::get('constants.HANDLE_SUCCESS'));
    }

    /**
     * 比赛投票
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function vote(Request $req) {
        $petId     = $req->get('petId');
        $matchType = $req->get('matchType');
        $poll      = $req->get('poll');

        //  缺少必填字段
        if (!$petId || !$poll || !$matchType || $poll <= 0) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        $matchOptions = Config::get('constants.MATCHES_OPTIONS');

        //  没有找到比赛
        if (!$matchOptions) return response()->json(Config::get('constants.NOT_FOUND_MATCH'));

        $matchOptions = $this->helper->parseMatchOptions($matchOptions, true);
        $matchInfo = array();
        foreach($matchOptions['lists'] as $v) {
            if ($v['matchType'] != $matchType) continue;
            $matchInfo = $v;
            break;
        }

        //  输入的参数错误，未匹配到相应的比赛
        if (!$matchInfo) return response()->json(Config::get('constants.VERFY_ARGS_ERROR'));

        $matchId  = $this->helper->getMatchId($matchType);

        //  宠物未参赛
        if (!$this->helper->checkRankingMemExist($matchType, $matchId, $petId))
            return response()->json(Config::get('constants.PETS_OUT_MATCH_ERROR'));

        $userInfo = Auth::guard('api')->user()->toArray();
        $userId   = $userInfo['id'];
        $wallet   = $userInfo['hlw_wallet'];

        //  投票次数已达上限
        if ($this->helper->getMatchVote($matchType, $userId) >= $matchInfo['voteLimit'])
            return response()->json(Config::get('constants.MATCH_VOTE_ERROR'));

        $cost = $matchInfo['voteCost'] + $poll;

        //  余额不足
        if ($wallet < $cost)
            return response()->json(Config::get('constants.WALLET_AMOUNT_ERROR'));

        //  花钱
        $this->userModel->updateUser($userId, ['hlw_wallet' => $wallet - $cost]);

        //  设置投票次数
        $this->helper->setMatchVote($matchType, $userId);

        //  投票
        $this->helper->setMatchRanking($matchType, $matchId, $petId, $poll);

        Log::info('vote match userId ' . $userId . ', matchType ' . $matchType . ', matchId' . $matchId . ', petId ' . $petId . ', poll ' . $poll);

        return response()->json(Config::get('constants.HANDLE_SUCCESS'));
    }

    /**
     * 获取历史排行榜
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRanking(Request $req) {
        $matchType  = $req->route('matchType');
        $sp         = $req->route('sp');        //  从哪开始取
        $row        = $req->route('row');       //  取多少条
        $rankingArr = array();

        //  缺少必填字段

        if (!$matchType || !$sp || !$row) return response()->json(Config::get('constants.DATA_EMPTY_ERROR'));

        //  获取往期比赛ID
        $matchIds = $this->helper->getMatchHisIds($matchType);

        rsort($matchIds);

        unset($matchIds[0]);

        $matchIds = array_slice($matchIds, $sp - 1, $row);

        $periodsArr = $this->helper->getPeriods($matchType, $matchIds);

        //  没有找到历史比赛
        if (!$matchIds) return response()->json(Config::get('constants.NOT_FOUND_HIS_MATCH'));

        //  获取排行榜信息
        foreach ($matchIds as $v) {
            $list = $this->helper->getMatchRanking($matchType, $v, 0, 2);
            $period = isset($periodsArr[$v]['period']) ? $periodsArr[$v]['period'] : 1;
            $flag = isset($periodsArr[$v]['flag']) ? $periodsArr[$v]['flag'] : 1;
            $rankingArr[] = [
                'matchId' => $v,
                'period'  => $period,
                'flag'    => $flag,
                'ranking' => $list ? $list : []
            ];
        }
        return response()->json(
            array_merge(
                [
                    'rankInfo' => $rankingArr
                ],
                Config::get('constants.HANDLE_SUCCESS')
            )
        );
    }
}
