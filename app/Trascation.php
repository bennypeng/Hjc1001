<?php

namespace App;

use App\Services\HelperService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
}
