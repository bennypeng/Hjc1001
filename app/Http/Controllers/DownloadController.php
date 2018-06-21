<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function index(Request $req)
    {
        $file = $req->route('file');

        $filePath = storage_path('download/'.$file);

        $fp = fopen($filePath, "r");
        header("Content-type: application/pdf");
        fpassthru($fp);
        fclose($fp);

        //return file_get_contents($filePath);
    }
}
