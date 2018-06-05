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
     * 检查用户是否存在
     * @param string $mobile 手机号码
     * @return null|object
     */
    function checkUserExist($mobile = '') {
        return Users::where('mobile', '=', $mobile)->first();
    }

    /**
     * 注册用户
     * @param string $mobile 手机号码
     * @param string $pwd 密码
     * @return bool
     */
    function registerUser($mobile = '', $pwd = '') {
        if (strlen(trim($mobile)) == 0 || strlen(trim($pwd)) == 0) return false;
        $usersModel = new Users;
        $usersModel->mobile   = $mobile;
        $usersModel->pwd      = $pwd;
        $usersModel->nickname = $mobile;
        return $usersModel->save();
    }

    /**
     * 更新用户信息
     * @param string $mobile 手机号码
     * @param array $data 更新字段信息
     * @return bool
     */
    function updateUser($mobile = '', $data = []) {
        if (strlen(trim($mobile)) == 0 || count($data) == 0) return false;
        $usersModel = new Users;
        foreach ($data as $k => $v) {
            $usersModel->$k = $v;
        }
        return $usersModel->save();
    }


}
