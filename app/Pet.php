<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $table      = 'pets';
    protected $primaryKey = 'id';
    public    $timestamps = false;
    //protected $dateFormat = 'U';

    /**
     * 创建一只宠物
     * @param array $data 更新字段信息
     * @return bool|int
     */
    public function createPet($data = []) {
        if (count($data) == 0) return false;
        return Pet::insertGetId($data);
    }

    /**
     * 获取宠物列表
     * @param int $type 1诞生列表 2拍卖列表
     * @return array
     */
    public function getPetLists($type = 1) {
        if ($type == 1) {
            $res = Pet::where('expired_at', '>', Carbon::now())
                ->where('ownerId', '=', 0)
                ->where('on_sale', '=', 2)
                ->get();
        } else {
            $res = Pet::where('expired_at', '>', Carbon::now())
                ->where('ownerId', '!=', 0)
                ->where('on_sale', '=', 2)
                ->get();
        }
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }

    /**
     * 获取用户宠物列表
     * @param string $userId 用户ID
     * @return array
     */
    public function getUserPetLists($userId = '') {
        if (!$userId) return array();
        $res = Pet::where('ownerId', '=', $userId)->get();
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }

    /**
     * 获取宠物信息
     * @param string $petId 宠物ID
     * @return array
     */
    public function getPetDetails($petId = '') {
        if (!$petId) return array();
        $res = Pet::where('id', '=', $petId)
            ->first();
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }


}
