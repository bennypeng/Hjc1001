<?php

namespace App\Services;

use App\Contracts\HelperContract;

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
}