<?php

namespace App;

use App\Services\HelperService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Extraction extends Model
{
    protected $helper;
    protected $table      = 'extractions';
    protected $primaryKey = 'id';
    public    $timestamps = false;


    function getExtractByAddress($address = '') {
        if (!$address) return array();
        $helper = new HelperService();
        $res = $helper->getExtractList($address);
        if (!$res) {
            $res = Extraction::where('address', '=', $address)->get();
            if (!$res || !is_object($res)) return array();
            $res = $res->toArray();
            foreach($res as $k => $v) {
                $helper->setExtract($address, $v);
                $res[$k]['time'] = strtotime($v['created_at']);
                unset($res[$k]['created_at'], $res[$k]['updated_at']);
            }
        }
        return $res;
    }

    function getExtract($id = '') {
        if (!$id) return array();
        $res = Extraction::where('id', '=', $id)->first();
        if (!$res || !is_object($res)) return array();
        $res = $res->toArray();
        return $res;
    }

    function setExtract($data = []) {
        if (count($data) == 0) return false;
        $helper = new HelperService();
        $helper->delExtract($data['address']);
        return Extraction::insertGetId($data);
    }

    function updateExtract($address = '', $id = '', $data = []) {
        if (strlen(trim($address)) == 0 || strlen(trim($id)) == 0 || count($data) == 0) return false;
        $res = false;
        DB::beginTransaction();
        $count = DB::table('extractions')->where('id', '=', $id)->lockForUpdate()->count();
        if ($count <= 1) {
            $res = DB::table('extractions')->where('id', '=', $id)
                ->update($data);
        }
        DB::commit();
        if ($res) {
            $helper = new HelperService();
            $helper->delExtract($address);
        }
        return $res;
    }
}
