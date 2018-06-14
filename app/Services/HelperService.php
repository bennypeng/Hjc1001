<?php

namespace App\Services;

use App\Contracts\HelperContract;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class HelperService implements HelperContract
{
    /**
     * 根据权重随机获取
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

    /*** 用户相关 ***/
    public function setUserInfo(string $userId, array $data) {
        $key = $this->getUserKey($userId);
        Redis::select(Config::get('constants.USERS_INDEX'));
        Redis::del($key);
        Redis::hmset($key, $data);
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
    public function parsePetDetails(array $data, bool $fullData = false) {
        $res = array();
        foreach($data as $k => $v) {
            $res[$v['id']] = array(
                'id' => $v['id'],
                'ownerId' => $v['ownerId'],
                'petType' => $v['type'],
                'price' => $v['price'],
                'on_sale' => $v['on_sale'],
                'rarity' => $this->calcRarity($v)
            );
            if ($fullData) {

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
    public function getAmount() {
        $key = $this->getAmountKey();
        Redis::select(Config::get('constants.PETS_INDEX'));
        $num = Redis::get($key);
        if (is_null($num)) {
            $num = 0;
            $this->setAmount($num);
        }
        return $num;
    }
    public function setAmount(int $num) {
        $key = $this->getAmountKey();
        Redis::select(Config::get('constants.PETS_INDEX'));
        Redis::incrby($key, $num);
    }

    /*** KEY ***/
    public function getUserKey(string $userId) {
        return 'U:' . $userId;
    }
    public function getMobileKey(string $mobile) {
        return 'M:' . $mobile;
    }
    public function getAmountKey() {
        return 'PET:AMOUNT';
    }
    public function getCoolTimeKey() {
        return 'PET:COOLTIME';
    }
}