<?php

namespace App;

use App\Services\HelperService;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{

    public function getMatchLists() {
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
}
