<?php

namespace App;

use App\Services\HelperService;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{

    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mobile', 'nickname', 'password', 'address', 'eth_wallet', 'hlw_wallet', 'icon', 'eth_lock_wallet', 'hlw_lock_wallet', 'agent_level', 'invite_code'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'password',
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
        $helper = new HelperService();
        $res = $helper->getUserInfo($userId);
        if (!$res) {
            $res = User::where('id', '=', $userId)->first();
            if (!$res || !is_object($res)) return array();
            $res = $res->toArray();
            $helper->setUserInfo($userId, $res);
        }
        return $res;
    }

    /**
     * 注册用户
     * @param array $data 更新字段信息
     * @return bool|int
     */
    function registerUser($data = []) {
        if (count($data) == 0) return false;
        return User::insertGetId($data);
    }

    /**
     * 更新用户信息
     * @param string $userId 用户ID
     * @param array $data 更新字段信息
     * @return bool
     */
    function updateUser($userId = '', $data = []) {
        if (strlen(trim($userId)) == 0 || count($data) == 0) return false;
        $res = User::where('id', '=', $userId)
            ->update($data);
        if ($res) {
            $helper = new HelperService();
            $helper->delUserInfo($userId);
        }
        return $res;
    }

    /**
     * 通过address获取userId
     * @param $address
     * @return mixed|string
     */
    function getUserIdByAddress($address) {
        if (!$address) return '';
        $res = User::where('address', '=', $address)->first();
        if (!$res || !is_object($res)) return '';
        return $res->id;
    }

    /**
     * 通过邀请码获取userId
     * @param $inviteCode
     * @return array
     */
    function getUserByInviteCode($inviteCode) {
        if (!$inviteCode) return array();
        $res = User::where('invite_code', '=', $inviteCode)->first();
        if (!$res || !is_object($res)) return array();
        return $res;
    }


}
