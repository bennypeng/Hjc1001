<?php

return [

    /**
     * 服务器及其他信息
     */
    'SERVER_IP'                => '192.168.10.10',          //  服务器IP
    'PUBLISH_TIME'             => '2018-05-01 00:00:00',    //  发布时间
    'PET_BIRTH_LIMIT'          => 12,                       //  宠物出生数量限制
    'PET_START_PRICE'          => 30,                       //  宠物起始价格
    'PET_FINAL_PRICE'          => 15,                       //  宠物终止价格
    'PET_SALE_EXP_SEC'         => 86400,                    //  宠物拍卖时长

    /**
     * Redis数据库相关
     */
    'USERS_INDEX'              => 0,    //  用户信息
    'LOGIN_INDEX'              => 1,    //  登录信息
    'PETS_INDEX'               => 2,    //  宠物信息
    'MATCHES_INDEX'            => 3,    //  比赛信息
    'RANKING_INDEX'            => 4,    //  排行榜信息
    'MOBILES_INDEX'            => 5,    //  手机信息

    /**
     * 操作返回码相关
     */
    'LOGIN_SUCCESS'            => ['message' => '登录成功',                     'code' => 10000],
    'LOGIN_ERROR'              => ['message' => '登录失败',                     'code' => 10001],
    'LOGIN_OUT'                => ['message' => '登出成功',                     'code' => 10002],
    'LOGIN_OUT_ERROR'          => ['message' => '登出失败',                     'code' => 10003],

    'REGIST_ERROR'             => ['message' => '注册失败',                     'code' => 10010],
    'REGIST_SUCCESS'           => ['message' => '注册成功',                     'code' => 10011],

    'UPDATE_SUCCESS'           => ['message' => '修改成功',                     'code' => 10020],
    'UPDATE_ERROR'             => ['message' => '修改失败',                     'code' => 10021],

    'NOT_FOUND_USER'           => ['message' => '未找到该账号，请注册',         'code' => 10030],
    'NOT_FOUND_PET'            => ['message' => '未找到该宠物',                 'code' => 10031],
    'NOT_FOUND_MATCH'          => ['message' => '未找到比赛',                   'code' => 10032],
    'NOT_FOUND_HIS_MATCH'      => ['message' => '未找到历史比赛',               'code' => 10033],

    'VERFY_CODE_ERROR'         => ['message' => '验证码错误',                   'code' => 10041],
    'VERFY_IP_ERROR'           => ['message' => '请求来源错误',                 'code' => 10042],
    'VERFY_TOKEN_ERROR'        => ['message' => 'token验证错误',                'code' => 10043],
    'VERFY_ARGS_ERROR'         => ['message' => '参数验证错误',                 'code' => 10044],

    'PETS_COOLTIME_ERROR'      => ['message' => '出生冷却时间未到',             'code' => 10050],
    'PETS_ATTR_MAX_ERROR'      => ['message' => '该属性已达上限',               'code' => 10051],
    'PETS_ATTR_RE_ERROR'       => ['message' => '重复升级',                     'code' => 10052],
    'PETS_OWNER_ERROR'         => ['message' => '不是宠物的主人',               'code' => 10053],
    'PETS_OWNER_BUY_ERROR'     => ['message' => '你就是该宠物的主人啦',         'code' => 10054],
    'PETS_OUT_EXP_ERROR'       => ['message' => '宠物已下架',                   'code' => 10055],
    'PETS_ON_MATCH_ERROR'      => ['message' => '宠物正在参加比赛',             'code' => 10056],
    'PETS_ON_SALE_ERROR'       => ['message' => '宠物正在出售',                 'code' => 10057],
    'PETS_MATCH_TYPE_ERROR'    => ['message' => '参赛宠物类型错误',             'code' => 10058],
    'PETS_OUT_MATCH_ERROR'     => ['message' => '宠物未参赛',                   'code' => 10059],


    'HANDLE_SUCCESS'           => ['message' => '操作成功',                     'code' => 10060],
    'HANDLE_ERROR'             => ['message' => '操作失败',                     'code' => 10061],

    'DATA_MATCHING_ERROR'      => ['message' => '数据匹配失败',                 'code' => 10070],
    'DATA_INSERT_ERROR'        => ['message' => '数据入库失败',                 'code' => 10071],
    'DATA_EMPTY_ERROR'         => ['message' => '必要信息不能为空',             'code' => 10072],
    'DATA_FORMAT_ERROR'        => ['message' => '数据格式错误',                 'code' => 10073],

    'ALREADY_EXIST_MOBILE'     => ['message' => '该手机号已被注册，请直接登录', 'code' => 10080],
    'ALREADY_EXIST_USER'       => ['message' => '该用户已注册，请直接登录',     'code' => 10081],

    'WALLET_AMOUNT_ERROR'      => ['message' => '余额不足',                     'code' => 10090],

    'MATCH_LEN_ERROR'          => ['message' => '该比赛人数已达上限',           'code' => 10100],
    'MATCH_TYPE_ERROR'         => ['message' => '比赛类型错误',                 'code' => 10101],
    'MATCH_VOTE_ERROR'         => ['message' => '比赛次数上限',                 'code' => 10102],

    /**
     * 宠物参数设置
     * 宠物ID => [发布的第几天开放, 出生概率， 成长系数， 数量限制]
     */
    'PETS_OPTIONS'             => [
        1 => [0, 160, 1, -1],
        2 => [0, 240, 1, -1],
        3 => [0, 100, 1, -1],
        4 => [7,  80, 1, -1],
        5 => [7, 180, 1, -1],
        6 => [7, 240, 1, -1],
        7 => [14, 30, 1.2, 2000],
        8 => [14, 50, 1.2, 2000],
        9 => [14, 20, 1.2, 2000],
    ],

    /**
     * 宠物成长设置
     * 级别 => [成长值， 消耗的代币数量]
     */
    'PETS_STRENGTH_OPTIONS'    => [
        0 => [0, 0],
        1 => [10, 30],
        2 => [30, 90],
        3 => [60, 180],
        4 => [100, 300],
        5 => [150, 450],
        6 => [250, 750],
    ],

    /**
     * 宠物属性设置
     * 级别 => [属性值， 消耗的代币数量]
     */
    'PETS_ATTRIBUTE_OPTIONS'   => [
        0 => [0, 0],
        1 => [10, 1000],
        2 => [20, 1100],
        3 => [30, 1300],
        4 => [40, 1600],
        5 => [50, 2000],
        6 => [60, 2500],
    ],

    /**
     * 宠物装饰消耗的代币数量
     */
    'PETS_DECORATION_COST'   => 60,

    /**
     * 比赛设置
     * 比赛ID => [[允许参赛的宠物类型]， 参加比赛需要消耗的代币，最大人数，单次比赛投票次数限制， 单次投票消耗的代币，[[周几00:00到周几23:59:59], [周几00:00到周几23:59:59]]]
     */
    'MATCHES_OPTIONS'        => [
        1 => [[2, 4],    100, 100, 5, 10, [[1, 3], [4, 6]]],
        2 => [[1, 9],    100, 100, 5, 10, [[1, 3], [4, 6]]],
        3 => [[3, 5, 6], 100, 100, 5, 10, [[1, 3], [4, 6]]],
        4 => [[7, 8],    100, 100, 5, 10, [[1, 3], [4, 6]]]
    ],

    'MATCHES_REWARDS'         => "一等奖：电影广告植入（需审核）、正品公仔1、电影票3张、表情包1套#二等奖：正品公仔1、电影票3张、表情包1套#三等奖：电影票3张，表情包1套",



];