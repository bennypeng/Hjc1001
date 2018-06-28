<?php

namespace App\Services;

use App\Contracts\HelperContract;
use Carbon\Carbon;
use App\User;
use App\Pet;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class HelperService implements HelperContract
{
    /**
     * 通过权重获取随机数
     * @param array $weightValuesArr
     * @return int|string
     */
    public function getRandomByWeight(array $weightValuesArr) {
        $rand = mt_rand(1, (int) array_sum($weightValuesArr));

        foreach ($weightValuesArr as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
    }

    /**
     * 十进制转二进制，并按指定格式输出
     * @param int $number   十进制数
     * @param int $minLen  输出的最小长度
     * @return array
     */
    public function parseNum2Bit(int $number, int $minLen = 6) {
        $binData = decbin($number);
        $binLen  = strlen($binData);
        if ($binLen < $minLen)
            $binData = sprintf('%0' . $minLen . 's', $binData);
        return str_split($binData);
    }

    /**
     * 转换成包含布尔型的数组输出
     * @param array $numbers    包含0|1的数组
     * @return array
     */
    public function parseNums2Bool(array $numbers) {
        foreach ($numbers as &$v) {
            $v = $v == 1 ? true : false;
        }
        return $numbers;
    }

    /**
     * 转换成包含0|1的数组输出
     * @param array $bools    包含true|false的数组
     * @return array
     */
    public function parseBools2Nums(array $bools) {
        foreach ($bools as &$v) {
            $v = $v ? 1 : 0;
        }
        return $bools;
    }

    /**
     * 获取指定范围内的所有时间戳
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    public function generateDateRange(Carbon $start_date, Carbon $end_date) {
        $dates = [];
        for($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->timestamp;
        }
        return $dates;
    }

    /**
     * 获取指定长度随机码，最大4
     * @param int $len 长度
     * @return bool|string
     */
    public function generateRandomCode(int $len) {
        return substr(strval(rand(10000,19999)),1,$len);
    }


    /**
     * 生成邀请码
     * @param string $mobile 手机号
     * @return string
     */
    public function createInviteCode(string $mobile) {
        static $sourceString = [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            'a', 'b', 'c', 'd', 'e', 'f',
            'g', 'h', 'i', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'q', 'r',
            's', 't', 'u', 'v', 'w', 'x',
            'y', 'z'
        ];

        $num = $mobile;
        $code = '';
        while ($num) {
            $mod = $num % 36;
            $num = (int)($num / 36);
            $code = "{$sourceString[$mod]}{$code}";
        }

        //判断code的长度
        if (empty($code[4]))
            str_pad($code, 5, '0', STR_PAD_LEFT);

        return $code;
    }

        /*** 用户相关 ***/
    public function setUserInfo(string $userId, array $data) {
        $key = $this->getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        Redis::del($key);
        Redis::hmset($key, $data);
    }
    public function delUserInfo(string $userId) {
        $key = $this->getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        Redis::del($key);
    }
    public function getUserInfo(string $userId) {
        $key = $this->getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        return Redis::hgetall($key);
    }
    public function setMobile(string $mobile) {
        $key = $this->getMobileKey($mobile);
        Redis::select(Config::get('constants.MOBILES_INDEX'));
        Redis::set($key, 1);
    }
    public function checkMobileExist(string $mobile) {
        $key = $this->getMobileKey($mobile);
        Redis::select(Config::get('constants.MOBILES_INDEX'));
        return Redis::exists($key);
    }

    /*** 宠物相关 ***/
    public function setPetInfo(string $petId, array $data) {
        $key = $this->getPetKey($petId);
        Redis::select(Config::get('constants.PETS_INDEX'));
        Redis::del($key);
        Redis::hmset($key, $data);
        Redis::expireat($key, Carbon::now()->addDay(7)->timestamp);
    }
    public function delPetInfo(string $petId) {
        $key = $this->getPetKey($petId);
        Redis::select(Config::get('constants.PETS_INDEX'));
        Redis::del($key);
    }
    public function getPetInfo(string $petId) {
        $key = $this->getPetKey($petId);
        Redis::select(Config::get('constants.PETS_INDEX'));
        return Redis::hgetall($key);
    }
    public function calcRarity(array $petInfo) {
        //  稀有值 = (体力值+属性值+装饰完整度) * 成长系数 + 随机系数
        list($petOptions, $petStrengthOptions, $petAttributeOptions) = array_values(Config::getMany(
            ['constants.PETS_OPTIONS', 'constants.PETS_STRENGTH_OPTIONS', 'constants.PETS_ATTRIBUTE_OPTIONS']
        ));
        return round(
            (
                $petStrengthOptions[$petInfo['attr1']][0]
                + $petAttributeOptions[$petInfo['attr2']][0]
                + $petInfo['attr3']
            ) * $petOptions[$petInfo['type']][2] + $petInfo['attr4']
        );
    }
    public function calcPrice(float $sp, float $fp, $expTimestamp) {
        $ts = time();
        if ($ts >= $expTimestamp)
            return $fp;
        $bal = $sp - $fp;   //  起止价差额
        $perHourBal = $bal / 24;    //  每小时降价多少
        $inHours = ceil(($ts - ($expTimestamp - 86400)) / 3600); //  过了多少小时
        $price = $perHourBal == 0 ? $fp : $sp - $perHourBal * $inHours;
        return round($price, 6);
    }
    public function parsePetDetails(array $data, bool $fullData = false) {
        $res = array();
        if ($fullData) {
            list($petStrengthOptions, $petAttributeOptions, $decorationCost) = array_values(Config::getMany(
                ['constants.PETS_STRENGTH_OPTIONS', 'constants.PETS_ATTRIBUTE_OPTIONS', 'constants.PETS_DECORATION_COST']
            ));
        }

        foreach($data as $k => $v) {
            $expTs = strtotime($v['expired_at']);
            //if ($v['ownerId'] == 0) {
            //    $price = $v['sp'];  //  系统诞生的，当前价不变
            //} else {
            $price = $this->calcPrice($v['sp'], $v['fp'], $expTs);
            //}
            $res[$v['id']] = array(
                'id'         => $v['id'],
                'ownerId'    => $v['ownerId'],
                'petType'    => $v['type'],
                'on_sale'    => $v['on_sale'],
                'matchId'    => $v['matchId'],
                'rarity'     => $this->calcRarity($v),
                'price'      => $price,                //   当前价格，需要根据拍卖时长来计算
                'exp'        => $expTs
            );
            if ($fullData) {
                $petStrengthVal       = isset($petStrengthOptions[$v['attr1']][0]) ? $petStrengthOptions[$v['attr1']][0] : 0;
                $petAttributeVal      = isset($petAttributeOptions[$v['attr2']][0]) ? $petAttributeOptions[$v['attr2']][0] : 0;
                $petStrengthCost      = isset($petStrengthOptions[$v['attr1']][1]) ? $petStrengthOptions[$v['attr1']][1] : 999;
                $petAttributeCost     = isset($petAttributeOptions[$v['attr2']][1]) ? $petAttributeOptions[$v['attr2']][1] : 999;

                $petStrengthNextVal   = isset($petStrengthOptions[$v['attr1'] + 1][0]) ? $petStrengthOptions[$v['attr1'] + 1][0] : 0;
                $petAttributeNextVal  = isset($petAttributeOptions[$v['attr2'] + 1][0]) ? $petAttributeOptions[$v['attr2'] + 1][0] : 0;
                $petStrengthNextCost  = isset($petStrengthOptions[$v['attr1'] + 1][1]) ? $petStrengthOptions[$v['attr1'] + 1][1] : 999;
                $petAttributeNextCost = isset($petAttributeOptions[$v['attr2'] + 1][1]) ? $petAttributeOptions[$v['attr2'] + 1][1] : 999;

                $res[$v['id']]['startPrice'] = $v['sp'];             //   起价
                $res[$v['id']]['finalPrice'] = $v['fp'];             //   终价
                $res[$v['id']]['strength'] = [
                    'maxLevel' => max(array_keys($petStrengthOptions)),
                    'current' => [
                        'level' => (int)$v['attr1'],
                        'value' => $petStrengthVal,
                        'cost'  => $petStrengthCost
                    ],
                    'next' => [
                        'level' => (int)$v['attr1'] + 1,
                        'value' => $petStrengthNextVal,
                        'cost'  => $petStrengthNextCost
                    ],

                ];
                $res[$v['id']]['attribute'] = [
                    'maxLevel' => max(array_keys($petAttributeOptions)),
                    'current' => [
                        'level' => (int)$v['attr2'],
                        'value' => $petAttributeVal,
                        'cost'  => $petAttributeCost
                    ],
                    'next' => [
                        'level' => (int)$v['attr2'] + 1,
                        'value' => $petAttributeNextVal,
                        'cost'  => $petAttributeNextCost
                    ],
                ];
                $res[$v['id']]['decoration'] = $this->parseNums2Bool($this->parseNum2Bit($v['attr3']));
                $res[$v['id']]['decorationCost'] = $decorationCost;
            }
        }
        krsort($res);
        return array_values($res);
    }
    public function generatePet() {
        $allowList = array();
        $publishTs      = strtotime(Config::get('constants.PUBLISH_TIME'));
        $ts             = time();

        foreach(Config::get('constants.PETS_OPTIONS') as $k => $v) {
            if ($ts < $v[0] * 86400 + $publishTs) continue;
            $allowList[$k] = $v[1];
        }
        if (empty($allowList)) return false;

        return $this->getRandomByWeight($allowList);
    }
    public function getCoolTime() {
        $key = $this->getCoolTimeKey();
        Redis::select(Config::get('constants.PETS_INDEX'));
        $ts = Redis::get($key);
        if (is_null($ts)) {
            $ts = time();
            $this->setCoolTime($ts);
        }
        return $ts;
    }
    public function setCoolTime(int $ts) {
        $key = $this->getCoolTimeKey();
        Redis::select(Config::get('constants.PETS_INDEX'));
        Redis::set($key, $ts);
    }

    /*** 比赛相关 ***/
    public function parseMatchOptions(array $data, bool $fullData = false) {

        $res = array();

        //  获取一周的时间戳
        if ($fullData) {
            $from = Carbon::now()->startOfWeek();
            $to = Carbon::now()->endOfWeek();
            $tsArr = $this->generateDateRange($from, $to);
        }

        foreach ($data as $k => $v) {
            $res['lists'][$k] = [
                'matchType'  => $k,
                'allowTypes' => $v[0],
                'cost'       => $v[1]
            ];
            if ($fullData) {
                $timeArr = array();
                foreach($v[5] as $j => $idxArr) {
                    $timeArr[$j][] = $tsArr[$idxArr[0] - 1];
                    $timeArr[$j][] = $tsArr[$idxArr[1] - 1] + 86400 - 1;
                }
                $res['lists'][$k]['openTime'] = $timeArr;
                $res['lists'][$k]['joinLimit'] = $v[2];
                $res['lists'][$k]['voteLimit'] = $v[3];
                $res['lists'][$k]['voteCost'] = $v[4];
            }
        }
        $res['lists']   = array_values($res['lists']);
        $res['rewards'] = Config::get('constants.MATCHES_REWARDS');
        return $res;
    }
    public function setMatchId(int $matchType) {
        $key = $this->getMatchCurIdKey($matchType);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        $st   = Carbon::now()->startOfWeek()->format('Ymd');
        $ft   = Carbon::now()->endOfWeek()->format('Ymd');
        $flag = Carbon::now()->dayOfWeek <= 3 ? 1 : 2;
        $id   = $st . "_" . $ft . "_" . $flag;
        Redis::set($key, $id);
        return $id;
    }
    public function setMatchCoolTime(int $matchType, int $ts) {
        $key = $this->getMatchCoolTimeKey($matchType);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        Redis::set($key, $ts);
        Redis::expireat($key, $ts);
    }
    public function setMatchHisIds(int $matchType, string $matchId) {
        $key = $this->getMatchHisIdsKey($matchType);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        Redis::hset($key, $matchId, 1);
    }
    public function getMatchCoolTime(int $matchType) {
        $key = $this->getMatchCoolTimeKey($matchType);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        return Redis::get($key);
    }
    public function getMatchId(int $matchType) {
        $key = $this->getMatchCurIdKey($matchType);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        return Redis::get($key);
    }
    public function getMatchHisIds(int $matchType) {
        $key = $this->getMatchHisIdsKey($matchType);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        return Redis::hkeys($key);
    }
    public function setMatchRanking(int $matchType, string $matchId, int $petId, int $voteNums) {
        $key = $this->getMatchRankingKey($matchType, $matchId);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        Redis::zincrby($key, $voteNums, $petId);
    }
    public function getMatchRanking(int $matchType, string $matchId, int $min, int $max) {
        $ret       = array();
        $petModel  = new Pet;
        $userModel = new User;
        $key       = $this->getMatchRankingKey($matchType, $matchId);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        $ranking   = Redis::zrevrange($key, $min, $max, 'withscores');

        foreach($ranking as $petId => $scores) {
            $petDetails = $petModel->getPetDetails($petId);
            $userInfo   = $userModel->getUserByUserId($petDetails['ownerId']);
            $ret[] = [
                'petId'    => $petId,
                'ticket'   => (int) $scores,
                'icon'     => $userInfo['icon'],
                'nickname' => $userInfo['nickname']
            ];
        }
        return $ret;
    }
    public function getMatchRankingLen(int $matchType, string $matchId) {
        $key = $this->getMatchRankingKey($matchType, $matchId);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        return Redis::zcard($key);
    }
    public function getMatchTypeByPetType(int $petType) {
        $matchType = false;
        $matchOptions = Config::get('constants.MATCHES_OPTIONS');
        foreach($matchOptions as $k => $v) {
            if (in_array($petType, $v[0])) {
                $matchType = $k;
                break;
            }
        }
        return $matchType;
    }
    public function getPeriods(int $matchType, array $matchIds) {
        $hisArr = $ret = array();
        $matchHisIds = $this->getMatchHisIds($matchType);

        foreach($matchHisIds as $k => $v) {
            $str = substr($v,0,strlen($v) - 2);
            $hisArr[] = $str;
        }
        $hisArr = array_unique($hisArr);
        sort($hisArr);
        foreach($matchIds as $v) {
            list($st, $ft, $flag) = explode('_', $v);
            $str = $st . '_' . $ft;
            $ret[$v] = [
                'period' => array_search($str, $hisArr) + 1,        //    第几期
                'flag'   => (int) $flag                             //    第几次
            ];
        }
        return $ret;
    }
    public function checkRankingMemExist(int $matchType, string $matchId, $petId) {
        $key = $this->getMatchRankingKey($matchType, $matchId);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        return is_null(Redis::zscore($key, $petId)) ? false : true;
    }
    public function getMatchVote(int $matchType, string $userId) {
        $key = $this->getMatchVoteKey($matchType, $userId);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        if (!Redis::exists($key)) {
            Redis::set($key, 0);
            Redis::expireat($key, $this->getMatchCoolTime($matchType));
            return 0;
        }
        return Redis::get($key);
    }
    public function setMatchVote(int $matchType, string $userId) {
        $key = $this->getMatchVoteKey($matchType, $userId);
        Redis::select(Config::get('constants.MATCHES_INDEX'));
        Redis::incr($key);
    }

    /*** 验证码相关 ***/
    public function reqVerfyCode(string $mobile) {
        $client = new Client;
        $randomCode = $this->generateRandomCode(4);
        $resp = $client->request('POST', env('MESSAGE_SEND_URL'), [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
            ],
            'form_params' => [
                'accesskey' => env('MESSAGE_ACCESSKEY'),
                'secret' => env('MESSAGE_ACCESSSECRET'),
                'sign' => env('MESSAGE_HLW_SIGN'),
                'templateId' => env('MESSAGE_HLW_TEMPID'),
                'mobile' => '86'. $mobile,
                'content' => $randomCode
            ]
        ]);
        /**
         * @todo 手机区号
         */
        $ret = json_decode($resp->getBody()->getContents(), true);
        if ($ret['msg'] == 'SUCCESS') {
            $this->setVerfyCode($mobile, $randomCode);
            return $randomCode;
        }
        return false;
    }
    public function getVerfyCode(string $mobile) {
        $key = $this->getVerfyCodeKey($mobile);
        Redis::select(Config::get('constants.VERFY_CODE_INDEX'));
        return Redis::get($key);
    }
    public function setVerfyCode(string $mobile, string $code){
        $key = $this->getVerfyCodeKey($mobile);
        Redis::select(Config::get('constants.VERFY_CODE_INDEX'));
        Redis::set($key, $code);
        Redis::expireat($key, Carbon::now()->addMinute(1)->timestamp);
    }
    public function getVerfyCodeLimit(string $ip) {
        $key = $this->getVerfyCodeLimitKey($ip);
        Redis::select(Config::get('constants.VERFY_CODE_INDEX'));
        if (!Redis::exists($key)) {
            Redis::set($key, 0);
            Redis::expireat($key, Carbon::now()->endOfDay()->timestamp);
        }
        return Redis::get($key);
    }
    public function setVerfyCodeLimit(string $ip) {
        $key = $this->getVerfyCodeLimitKey($ip);
        Redis::select(Config::get('constants.VERFY_CODE_INDEX'));
        Redis::incr($key);
    }

    /*** 以太坊接口相关 ***/
    public function setEthTransaction(string $action, array $data) {
        $key = $this->getEthTransactionKey($action);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        Redis::hmset($key, $data);
    }
    public function getEthTransaction(string $action) {
        $key = $this->getEthTransactionKey($action);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        return Redis::hgetall($key);
    }
    public function setEthToAddress(string $address, string $tx, array $data) {
        $key = $this->getEthToAddressKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        Redis::hset($key, $tx, $data);
    }
    public function getEthToAddress(string $address) {
        $key = $this->getEthToAddressKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        return Redis::hgetall($key);
    }
    public function setEthFromAddress(string $address, string $tx, array $data) {
        $key = $this->getEthFromAddressKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        Redis::hset($key, $tx, $data);
    }
    public function getEthFromAddress(string $address) {
        $key = $this->getEthFromAddressKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        return Redis::hgetall($key);
    }
    public function getEthHandle(string $tx) {
        $key = $this->getEthHandleKey($tx);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        return Redis::hget($key, $tx);
    }
    public function setEthHandle(string $tx, int $status) {
        $key = $this->getEthHandleKey($tx);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        Redis::hset($key, $tx, $status);
    }
    public function getEthMd5(string $action) {
        $key = $this->getEthMd5Key($action);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        return Redis::get($key);
    }
    public function setEthMd5(string $action, string $mdstr) {
        $key = $this->getEthMd5Key($action);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        Redis::set($key, $mdstr);
    }

    /*** 提现充值相关 ***/
    public function getExtractList(string $address) {
        $ret = array();
        $key = $this->getExtractKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        $list = Redis::hgetall($key);
        foreach($list as $k => $v) {
            $arr = json_decode($v, true);
            $ret[$k] = [
                'id'     => $k,
                'userid'  => $arr['userid'],
                'address'  => $arr['address'],
                'money'  => $arr['money'],
                'flag'   => strtoupper($arr['flag']),
                'time'   => strtotime($arr['created_at']),
                'status' => $arr['status']
            ];
        }
        ksort($ret);
        return array_values($ret);
    }
    public function setExtract(string $address, array $data) {
        $key = $this->getExtractKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        Redis::hset($key, $data['id'], json_encode($data));
    }
    public function delExtract(string $address) {
        $key = $this->getExtractKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        Redis::del($key);
    }
    public function checkExtractExist(string $address, int $id) {
        $key = $this->getExtractKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        return Redis::hexists($key, $id);
    }
    public function setExtractStatus(string $address, int $id, int $status) {
        $key = $this->getExtractKey($address);
        Redis::select(Config::get('constants.ETH_TX_INDEX'));
        $list = Redis::hget($key, $id);
        $arr = json_decode($list, true);
        $arr['status'] = $status;
        Redis::hset($key, $id, json_encode($arr));
    }


    /*** 其他 ***/
    public function setAddressUserId(string $address, string $userId) {
        $key = $this->getAddressUserIdKey();
        Redis::select(Config::get('constants.USERS_INDEX'));
        Redis::hset($key, $address, $userId);
    }
    public function getAddressUserId(string $address) {
        $key = $this->getAddressUserIdKey();
        Redis::select(Config::get('constants.USERS_INDEX'));
        $userId = Redis::hget($key, $address);
        if (!$userId) {
            $userModel = new User;
            $userId = $userModel->getUserIdByAddress($address);
            if ($userId)
                $this->setAddressUserId($address, $userId);
        }
        return $userId;
    }

    /*** KEY ***/
    public function getUserKey(string $userId) {
        return 'U:' . $userId;
    }
    public function getMobileKey(string $mobile) {
        return 'M:' . $mobile;
    }
    public function getCoolTimeKey() {
        return 'PET:COOLTIME';
    }
    public function getPetKey(string $petId) {
        return 'P:' . $petId;
    }
    public function getMatchKey(int $matchType, string $matchId) {
        return 'MATCH:' . $matchType . ":" . $matchId;
    }
    public function getMatchCoolTimeKey(int $matchType) {
        return 'MATCH:COOLTIME:' . $matchType;
    }
    public function getMatchHisIdsKey(int $matchType) {
        return 'MATCH:HISTORY:' . $matchType;
    }
    public function getMatchCurIdKey(int $matchType) {
        return 'MATCH:CURRENT:' . $matchType;
    }
    public function getMatchRankingKey(int $matchType, string $matchId) {
        return 'MATCH:RANKING:' . $matchType . ":" . $matchId;
    }
    public function getMatchVoteKey(int $matchType, string $userId) {
        return 'MATCH:VOTE:' . $matchType . ":" . $userId;
    }
    public function getVerfyCodeKey(string $mobile) {
        return 'VERFYCODE:' . $mobile;
    }
    public function getVerfyCodeLimitKey(string $ip) {
        return 'VERFYLIMIT:' . $ip;
    }
    public function getEthTransactionKey(string $action) {
        return strtoupper('ETH:' . $action);
    }
    public function getEthToAddressKey(string $address) {
        return 'ETH:TO' . $address;
    }
    public function getEthFromAddressKey(string $address) {
        return 'ETH:FROM' . $address;
    }
    public function getEthHandleKey(string $tx) {
        return 'ETH:HANDLE';
    }
    public function getEthMd5Key(string $action) {
        return 'ETH:MD5:' . $action;
    }
    public function getExtractKey(string $address) {
        return 'EXTRACT:' . $address;
    }
    public function getAddressUserIdKey() {
        return 'ADDRESS:USERID';
    }
}