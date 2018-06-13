<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{

    protected $table      = 'users';
    protected $primaryKey = 'id';
    public    $timestamps = false;
    //protected $dateFormat = 'U';

    /**
     * 通过用户ID获取用户信息
     * @param string $userId 用户ID
     * @return array
     */
    function getUserByUserId($userId = '') {
        if (!$userId) return array();
        $res = Users::where('id', '=', $userId)->first();
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }

    /**
     * 通过手机号获取用户信息
     * @param string $mobile
     * @return array
     */
    function getUserByMobile($mobile = '') {
        if (!$mobile) return array();
        $res = Users::where('mobile', '=', $mobile)->first();
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }

    /**
     * 注册用户
     * @param array $data 更新字段信息
     * @return bool|int
     */
    function registerUser($data = []) {
        if (count($data) == 0) return false;
        return Users::insertGetId($data);
    }

    /**
     * 更新用户信息
     * @param string $userId 用户ID
     * @param array $data 更新字段信息
     * @return bool
     */
    function updateUser($userId = '', $data = []) {
        if (strlen(trim($userId)) == 0 || count($data) == 0) return false;
        return Users::where('id', '=', $userId)
            ->update($data);
    }


}
