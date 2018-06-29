<?php

namespace App\Console\Commands;

use App\Trascation;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCoin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tx:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic send coin hlw/eth';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $txModel   = new Trascation();
        $userModel = new User();
        $uObj = $userModel->selectRaw('id as userid, address, hlw_wallet, eth_wallet');
        $mObj = DB::table(DB::raw("({$uObj->toSql()}) as u, trascations"))
            ->mergeBindings(
                $uObj->getQuery()
            )->select('*');
        $res = $mObj->whereRaw("`from` = address AND `to` = '" . Config::get('constants.ETH_ADDR') . "' AND status = '0'")
            ->get();
        if ($res && is_object($res) && count($res) > 0) {
            //  如果有没有下发的充值记录， 则开始下发
            foreach($res as $k => $v) {
                //  修改订单状态
                if ($txModel->updateTrascation($v->id, ['status' => 1])) {
                    if ($v->tokenSymbol == 'HLW') {
                        $update = ['hlw_wallet' => $v->hlw_wallet + round($v->value / 10000, 0)];
                    } else {
                        $update = ['eth_wallet' => $v->eth_wallet + round($v->value / 1000000000000000000, 4)];
                    }
                    //  给用户发积分
                    if ($userModel->updateUser($v->userid, $update)) {
                        Log::info('send coin success', array_merge(
                            ['txid' => $v->id, 'userid' => $v->userid],
                            $update
                        ));
                        $this->line(sprintf("# txid %u userid %s send coin success", $v->id, $v->userid));
                    } else {
                        Log::error('send coin error', $update);
                        $this->line(sprintf("# txid %u userid %s send coin error", $v->id, $v->userid));
                    }
                } else {
                    Log::error('send coin error', (array)$v);
                    $this->line(sprintf("# txid %u userid %s send coin error", $v->id, $v->userid));
                }
            }

        } else {
            $this->line('未找到待充值订单');
        }
    }
}
