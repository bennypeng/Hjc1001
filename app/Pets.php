<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pets extends Model
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
        return Pets::insertGetId($data);
    }

    /**
     * 获取宠物列表
     * @param int $type 列表类型
     * @return array
     */
    public function getPetLists($type = 1) {
        if ($type == 1) {
            $res = Pets::where('expired_at', '>', Carbon::now())
                ->where('ownerId', '=', 0)
                ->where('on_sale', '=', 2)
                ->get();
        } else {
            $res = Pets::where('expired_at', '>', Carbon::now())
                ->where('ownerId', '!=', 0)
                ->where('on_sale', '=', 2)
                ->get();
        }
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }

    /**
     * 获取宠物信息
     * @param string $id 宠物ID
     * @return array
     */
    public function getPetDetails($id = '') {
        if (!$id) return array();
        $res = Pets::where('id', '=', $id)
            ->first();
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }


}
