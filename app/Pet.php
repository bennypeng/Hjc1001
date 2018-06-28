<?php

namespace App;

use App\Services\HelperService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $helper;
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
        $helper = new HelperService();
        $res = $helper->getPetInfo($petId);
        if (!$res) {
            $res = Pet::where('id', '=', $petId)
                ->first();
            if (!$res || !is_object($res)) return array();
            $res = $res->toArray();
            $helper->setPetInfo($petId, $res);
        }
        return $res;
    }

    /**
     * 更新宠物信息
     * @param string $userId
     * @param string $petId
     * @param array $data
     * @return bool
     */
    function updatePet($userId = '', $petId = '', $data = []) {
        if (strlen(trim($userId)) == 0 || strlen(trim($petId)) == 0 || count($data) == 0) return false;
        $res = Pet::where('id', '=', $petId)
            ->where('ownerId', '=', $userId)
            ->update($data);
        if ($res) {
            $helper = new HelperService();
            $helper->delPetInfo($petId);
        }
        return $res;
    }

    /**
     * 获取没有过期的宠物数量
     * @return int
     */
    function getInExpPetsCounts() {
        return Pet::where('ownerId', '=', 0)
            ->where('expired_at', '>', Carbon::now())
            ->count();
    }

    /**
     * 删除失效的宠物
     * @throws \Exception
     */
    function delOutExpPets() {
        Pet::where('ownerId', '=', 0)
            ->where('expired_at', '<=', Carbon::now())
            ->delete();
    }


}
