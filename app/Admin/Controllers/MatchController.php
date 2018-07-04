<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Admin\Controllers\MatchViewController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class MatchController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('比赛管理');
            $content->description('比赛信息');
            $content->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $column->append(MatchViewController::matchList());
                });
                $row->column(3, function (Column $column) {
                    $column->append(MatchViewController::matchRanking());
                });
                //$row->column(6, function (Column $column) {
                //    $column->append(MatchViewController::matchList());
                //});
            });
        });
    }
}
