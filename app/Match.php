<?php

namespace App;

use App\Services\HelperService;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{

    public function getMatchDetails($matchType = '') {
        $helper = new HelperService();
        $res = $helper->getPetInfo($matchType);
        if (!$res) {
            $res = Pet::where('id', '=', $petId)
                ->first();
            if (!$res || !is_object($res)) return array();
            $res = $res->toArray();
            $helper->setPetInfo($petId, $res);
        }
        return $res;
    }
}
