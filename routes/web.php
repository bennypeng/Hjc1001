<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//  日志查询
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

//  白皮书
Route::get('download/{file}', function ($file) {
    $filePath = storage_path('download/'.$file);
    if (file_exists($filePath)) {
        $fp = fopen($filePath, "r");
        header("Content-type: application/pdf");
        fpassthru($fp);
        fclose($fp);
    }
});