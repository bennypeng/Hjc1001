<?php

namespace App\Contracts;

use Carbon\Carbon;

interface HelperContract
{
    public function getRandomByWeight(array $weightValuesArr);                          //  通过权重获取随机数
    public function parseNum2Bit(int $number, int $minLen = 6);                         //  十进制转二进制，并按指定格式输出
    public function parseNums2Bool(array $numbers);                                     //  包含0|1的数组转换成布尔数组输出
    public function parseBools2Nums(array $bools);                                      //  包含true|false的数组转换成数组输出
    public function generateDateRange(Carbon $start_date, Carbon $end_date);            //  获取指定范围内的所有时间戳
    public function generateRandomCode(int $len);                                       //  生成指定长度的随机数

    public function setMobile(string $mobile);                                          //  设置手机到缓存
    public function checkMobileExist(string $mobile);                                   //  检查手机是否已存在
    public function setUserInfo(string $userId, array $data);                           //  设置用户信息到缓存
    public function delUserInfo(string $userId);                                        //  从缓存删除用户信息
    public function getUserInfo(string $userId);                                        //  从缓存获取用户信息

    public function getCoolTime();                                                      //  获取出生冷却时间
    public function setCoolTime(int $ts);                                               //  设置出生冷却时间

    public function calcPrice(float $sp, float $fp, $expTimestamp);                     //  计算当前价
    public function calcRarity(array $petInfo);                                         //  计算稀有度
    public function parsePetDetails(array $data, bool $fullData = false);               //  解析宠物信息
    public function generatePet();                                                      //  生成宠物
    public function setPetInfo(string $petId, array $data);                             //  设置宠物信息到缓存
    public function delPetInfo(string $petId);                                          //  从缓存删除宠物信息
    public function getPetInfo(string $petId);                                          //  从缓存获取宠物信息

    public function parseMatchOptions(array $data, bool $fullData = false);             //  解析比赛配置
    public function setMatchCoolTime(int $matchType, int $ts);                          //  设置比赛冷却时间
    public function getMatchCoolTime(int $matchType);                                   //  获取比赛冷却时间
    public function setMatchId(int $matchType);                                         //  设置当前比赛ID
    public function getMatchId(int $matchType);                                         //  获取当前比赛ID
    public function setMatchHisIds(int $matchType, string $matchId);                    //  设置往期比赛ID
    public function getMatchHisIds(int $matchType);                                     //  获取往期比赛ID集合
    public function setMatchRanking(
        int $matchType, string $matchId, int $petId, int $voteNums);                    //  设置比赛排行榜
    public function getMatchRanking(
        int $matchType, string $matchId, int $min, int $max);                           //  获取比赛排行榜
    public function getMatchRankingLen(int $matchType, string $matchId);                //  获取比赛参赛人数
    public function getMatchTypeByPetType(int $petType);                                //  通过宠物ID获取比赛类型
    public function getPeriods(int $matchType, array $matchIds);                        //  获取比赛期数
    public function checkRankingMemExist(int $matchType, string $matchId, $petId);      //  检验宠物是否在排行榜内
    public function getMatchVote(int $matchType, string $userId);                       //  获取比赛已投票次数
    public function setMatchVote(int $matchType, string $userId);                       //  设置比赛投票次数

    public function reqVerfyCode(string $mobile);                                       //  发送验证码
    public function getVerfyCode(string $mobile);                                       //  获取验证码
    public function setVerfyCode(string $mobile, string $code);                         //  设置验证码
    public function getVerfyCodeLimit(string $ip);                                      //  获取当前IP地址请求验证码次数
    public function setVerfyCodeLimit(string $ip);                                      //  设置当前IP地址请求验证码次数

    public function setEthTransaction(string $action, array $data);                     //  设置以太坊交易信息
    public function getEthTransaction(string $action);                                  //  获取以太坊交易信息
    public function setEthToAddress(string $address, string $tx, array $data);          //  设置指定地址以太坊交易信息（转入）
    public function getEthToAddress(string $address);                                   //  获取指定地址以太坊交易信息（转入）
    public function setEthFromAddress(string $address, string $tx, array $data);        //  设置指定地址以太坊交易信息（提现）
    public function getEthFromAddress(string $address);                                 //  获取指定地址以太坊交易信息（提现）
    public function getEthHandle(string $tx);                                           //  获取该tx的状态
    public function setEthHandle(string $tx, int $status);                              //  设置该tx的状态
    public function getEthMd5(string $action);                                          //  获取以太坊MD5
    public function setEthMd5(string $action, string $mdstr);                           //  设置以太坊MD5

    public function getExtractList(string $address);                                    //  获取提现列表
    public function setExtractList(string $address, array $data);                       //  设置提现列表
    public function checkExtractExist(string $address, int $id);                        //  检查提现单是否存在
    public function setExtractStatus(string $address, int $id, int $status);            //  设置提现单的状态

    public function getCoolTimeKey();                                                   //  出生冷却时间KEY
    public function getUserKey(string $userId);                                         //  用户信息KEY
    public function getMobileKey(string $mobile);                                       //  手机KEY
    public function getPetKey(string $petId);                                           //  宠物信息KEY

    public function getMatchKey(int $matchType, string $matchId);                       //  比赛信息KEY
    public function getMatchCoolTimeKey(int $matchType);                                //  比赛冷却时间KEY
    public function getMatchHisIdsKey(int $matchType);                                  //  比赛往期ID集合KEY
    public function getMatchCurIdKey(int $matchType);                                   //  比赛当前ID KEY
    public function getMatchRankingKey(int $matchType, string $matchId);                //  比赛排行榜 KEY
    public function getMatchVoteKey(int $matchType, string $userId);                    //  比赛投票 KEY

    public function getVerfyCodeKey(string $mobile);                                    //  获取验证码KEY
    public function getVerfyCodeLimitKey(string $ip);                                   //  获取当前IP地址请求验证码次数KEY

    public function getEthTransactionKey(string $action);                               //  获取以太坊交易记录KEY
    public function getEthToAddressKey(string $address);                                //  获取以太坊发送到XXX地址KEY
    public function getEthFromAddressKey(string $address);                              //  获取以太坊从XXX地址转出KEY
    public function getEthHandleKey(string $tx);                                        //  获取管理后台操作KEY
    public function getEthMd5Key(string $action);                                       //  获取以太坊MD5 KEY
    public function getExtractKey(string $address);                                     //  获取提现 KEY

}