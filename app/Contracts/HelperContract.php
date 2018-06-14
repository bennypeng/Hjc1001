<?php

namespace App\Contracts;

interface HelperContract
{
    public function getRandomByWeight(array $weightValuesArr);

    public function setUserInfo(string $userId, array $data);
    public function setMobile(string $mobile);
    public function checkMobileExist(string $mobile);

    public function getCoolTime();
    public function setCoolTime(int $ts);

    public function getAmount();
    public function setAmount(int $num);

    public function calcRarity(array $petInfo);
    public function parsePetDetails(array $data, bool $fullData = false);
    public function generatePet();

    public function getAmountKey();
    public function getCoolTimeKey();
    public function getUserKey(string $userId);
    public function getMobileKey(string $mobile);
}