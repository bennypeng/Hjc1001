<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{

    //protected $table      = 'users';
    //protected $primaryKey = 'id';
    //public    $timestamps = false;
    //protected $dateFormat = 'U';


    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mobile', 'nickname', 'password', 'address'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }



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
