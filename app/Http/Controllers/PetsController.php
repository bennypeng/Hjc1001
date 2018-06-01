<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PetsController extends Controller
{
    public function __construct() {}

    public function home(Request $request) {
        $uin = $request->get('uin');
        $gold = $request->get('gold');
        dd($uin, $gold);
    }
}
