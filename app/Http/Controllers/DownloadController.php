<?php

namespace App\Http\Controllers;

use App\Contracts\HelperContract;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DownloadController extends Controller
{

    protected $helper;

    public function __construct(HelperContract $helper)
    {
        $this->helper     = $helper;
    }


    /**
     * 同步以太坊交易记录
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncEthTransaction(Request $req)
    {
        $action = $req->route('action');
        $address = $req->route('address');

        $client = new Client;
        $resp = $client->request('GET', Config::get('constants.ETH_API_URL'), [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8'
            ],
            'query' => [
                'module' => 'account',
                'action' => $action,
                'address' => $address,
                'sort' => 'desc'
            ]
        ]);

        $data = array();
        if ($resp->getStatusCode() == 200) {
            $arr = json_decode($resp->getBody(), true);
            if ($arr['status'] == 1 && $arr['message'] == 'OK' && count($arr['result']) > 0) {
                foreach ($arr['result'] as $k => $v) {
                    unset($v['confirmations']);
                    $data[$v['hash']] = json_encode($v);
                }
                $md5Str = md5(serialize($data));
                if ($this->helper->getEthMd5($action) != $md5Str) {
                    $this->helper->setEthTransaction($action, $data);
                    $this->helper->setEthMd5($action, $md5Str);
                }
            }
            return response()->json(Config::get('constants.HANDLE_SUCCESS'));
        }
        return response()->json(Config::get('constants.HANDLE_ERROR'));
    }

    public function getEthTransactionStatus(Request $req)
    {
        $txhash = $req->route('txhash');

        $client = new Client;
        $resp = $client->request('GET', Config::get('constants.ETH_API_URL'), [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8'
            ],
            'query' => [
                'module' => 'transaction',
                'action' => 'gettxreceiptstatus',
                'txhash' => $txhash
            ]
        ]);

        if ($resp->getStatusCode() == 200) {
            $arr = json_decode($resp->getBody(), true);
            if ($arr['status'] == 1 && $arr['result']['status'] == 1)
                return response('success');
        }
        return response('fail');
    }
}
