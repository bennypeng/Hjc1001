<?php

namespace App;

//use App\Services\HelperService;
//use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trascation extends Model
{
    protected $helper;
    protected $table      = 'trascations';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    /**
     * 获取交易记录
     * @param string $address 钱包地址
     * @return array
     */
    public function getTrascationsByAddress($address = '') {
        $res = Trascation::where('from', '=', $address)
            ->orWhere('to', '=', $address)
            ->get();
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }

    /**
     * 获取交易记录
     * @param string $id id
     * @return array
     */
    public function getTrascationById($id = '') {
        $res = Trascation::where('id', '=', $id)
            ->first();
        if (!$res || !is_object($res)) return array();
        return $res->toArray();
    }

    function updateTrascation($id = '', $data = []) {
        if (strlen(trim($id)) == 0 || count($data) == 0) return false;
        $res = false;
        DB::beginTransaction();
        $count = DB::table('trascations')->where('id', '=', $id)->lockForUpdate()->count();
        if ($count <= 1) {
            $res = DB::table('trascations')->where('id', '=', $id)
                ->update($data);
        }
        DB::commit();
        //if ($res) {
        //    $helper = new HelperService();
        //    $helper->delExtract($address);
        //}
        return $res;
    }


}
