<?php

namespace App\Admin\Controllers;

use App\Services\HelperService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class MatchViewController
{

    protected $helper;

    public function __construct()
    {
        Redis::select(Config::get('constants.MATCHES_INDEX'));
    }

    public static function matchList() {
        $helper = new HelperService();
        $matchOptions = Config::get('constants.MATCHES_OPTIONS');
        $matchList    = $helper->parseMatchOptions($matchOptions, true);
        foreach ($matchList['lists'] as $k => &$info) {
            foreach($info as $key => &$val) {
                if ($key == 'openTime') {
                    foreach($val as &$v) {
                        $v[2] = time() > $v[0] && time() < $v[1] ? true : false;
                        $v[0] = Carbon::createFromTimestamp($v[0])->format('Y-m-d H:i:s');
                        $v[1] = Carbon::createFromTimestamp($v[1])->format('Y-m-d H:i:s');
                    }
                }
            }
        }
        return view('match.list', compact('matchList'));
    }

    public static function matchRanking() {
        $helper = new HelperService();
        $matchType = $helper->getMatchType();
        $matchType = $matchType ? $matchType : 1;
        $matchId   = $helper->getMatchId($matchType);
        $list   = $helper->getMatchRanking($matchType, $matchId, 0, 99);
        $ranking['ranking'] = $list;
        $ranking['matchid'] = $matchId;
        return view('match.ranking', compact('ranking'));
    }
}
