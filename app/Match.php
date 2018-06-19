<?php

namespace App;

use App\Services\HelperService;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{

    public function getMatchDetails($matchType = '') {
        $helper = new HelperService();
        $res = $helper->getMatchInfo($matchType);
        //if (!) {

        //}
        return $res;
    }
}
