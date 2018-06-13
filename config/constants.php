<?php

return [

    /**
     * 服务器及其他信息
     */
    'SERVER_IP'                => '192.168.10.1',           //  服务器IP
    'PUBLISH_TIME'             => '2018-05-01 00:00:00',    //  发布时间
    'PET_BIRTH_LIMIT'          => 12,                       //  宠物出生数量限制

    /**
     * Redis数据库相关
     */
    'USERS_INDEX'              => 0,    //  用户信息
    'LOGIN_INDEX'              => 1,    //  登录信息
    'PETS_INDEX'               => 2,    //  宠物信息
    'MATCHES_INDEX'            => 3,    //  比赛信息
    'RANKING_INDEX'            => 4,    //  排行榜信息

    /**
     * 操作返回码相关
     */
    'LOGIN_SUCCESS'            => ['message' => '登录成功',                     'code' => 1000],
    'LOGIN_ERROR'              => ['message' => '登录失败',                     'code' => 1001],
    'LOGIN_OUT'                => ['message' => '登出成功',                     'code' => 1002],
    'LOGIN_OUT_ERROR'          => ['message' => '登出失败',                     'code' => 1003],
    'REGIST_ERROR'             => ['message' => '注册失败',                     'code' => 1004],
    'REGIST_SUCCESS'           => ['message' => '注册成功',                     'code' => 1005],
    'UPDATE_SUCCESS'           => ['message' => '修改成功',                     'code' => 1006],
    'UPDATE_ERROR'             => ['message' => '修改失败',                     'code' => 1007],
    'EMPTY_ERROR'              => ['message' => '必要信息不能为空',             'code' => 1008],
    'NOT_FOUND_USER'           => ['message' => '未找到该账号，请注册',         'code' => 1009],
    'VERFY_CODE_ERROR'         => ['message' => '验证码错误',                   'code' => 1010],
    'VERFY_IP_ERROR'           => ['message' => '请求来源错误',                 'code' => 1011],
    'VERFY_TOKEN_ERROR'        => ['message' => 'token验证错误',                'code' => 1012],
    'VERFY_ARGS_ERROR'         => ['message' => '参数验证错误',                 'code' => 1013],
    'PETS_AMOUNT_ERROR'        => ['message' => '出生数量已达上限',             'code' => 1014],
    'PETS_COOLTIME_ERROR'      => ['message' => '出生冷却时间未到',             'code' => 1015],
    'HANDLE_SUCCESS'           => ['message' => '操作成功',                     'code' => 1016],
    'HANDLE_ERROR'             => ['message' => '操作失败',                     'code' => 1017],
    'DATA_MATCHING_ERROR'      => ['message' => '数据匹配失败',                 'code' => 1018],
    'DATA_INSERT_ERROR'        => ['message' => '数据入库失败',                 'code' => 1019],
    'LIST_EMPTY'               => ['message' => '空列表',                       'code' => 1020],
    'ALREADY_EXIST_MOBILE'     => ['message' => '该手机号已被注册，请直接登录', 'code' => 1021],
    'ALREADY_EXIST_USER'       => ['message' => '该用户已注册，请直接登录',     'code' => 1022],

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
        1 => [10, 10],
        2 => [15, 30],
        3 => [30, 90],
        4 => [60, 180],
        5 => [100, 300],
        6 => [150, 450],
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



];