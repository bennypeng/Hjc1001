<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function index(Request $req)
    {
        $file = $req->route('file');

        $filePath = storage_path('download/'.$file);

        if (file_exists($filePath)) {
            $fp = fopen($filePath, "r");
            header("Content-type: application/pdf");
            fpassthru($fp);
            fclose($fp);
        }
    }
}
