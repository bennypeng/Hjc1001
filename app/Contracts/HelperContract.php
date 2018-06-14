<?php

namespace App\Contracts;

interface HelperContract
{
    public function getRandomByWeight(array $weightValuesArr);                          //  通过权重获取随机数

    public function setUserInfo(string $userId, array $data);                           //  设置用户信息到缓存
    public function setMobile(string $mobile);                                          //  设置手机到缓存
    public function checkMobileExist(string $mobile);                                   //  检查手机是否已存在

    public function getCoolTime();                                                      //  获取出生冷却时间
    public function setCoolTime(int $ts);                                               //  设置出生冷却时间

    public function getAmount();                                                        //  获取拍卖行待售宠物数量（出生）
    public function setAmount(int $num);                                                //  设置拍卖行待售宠物数量（出生）

    public function calcRarity(array $petInfo);                                         //  计算稀有度
    public function parsePetDetails(array $data, bool $fullData = false);               //  解析宠物信息
    public function generatePet();                                                      //  生成宠物
    public function setPetInfo(string $petId, array $data);                             //  设置宠物信息到缓存
    public function getPetInfo(string $petId);                                          //  从缓存获取宠物信息

    public function getAmountKey();                                                     //  拍卖行待售宠物数量KEY（出生）
    public function getCoolTimeKey();                                                   //  出生冷却时间KEY
    public function getUserKey(string $userId);                                         //  用户信息KEY
    public function getMobileKey(string $mobile);                                       //  手机KEY
    public function getPetKey(string $petId);                                           //  宠物信息KEY
}