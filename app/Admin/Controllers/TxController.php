<?php

namespace App\Admin\Controllers;

use App\Trascation;

use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class TxController extends Controller
{
    use ModelForm;

    public function getEthTxList()
    {

        return Admin::content(function (Content $content) {

            $content->header('ETH交易订单');
            //$content->description('description');

            $grid = Admin::grid(Trascation::class, function(Grid $grid) {

                $grid->id('ID')->sortable();

                $grid->column('userId', '用户ID')->display(function () {
                    return '#1111';
                });

                $grid->column('hash', '流水号');

                $grid->column('from', '发送方');

                $grid->column('direction', '去向')->display(function () {
                    /**
                     * @todo 这个地址发布时需要修改
                     */
                    if ($this->from == '0x36292dc34148a30fa50d7381a78a9c173bdfd3ac') {
                        return '<span class="label label-info rounded">OUT</span>';
                    } else {
                        return '<span class="label label-success rounded">&nbsp; IN &nbsp;</span>';
                    }

                });

                $grid->column('to', '接收方');

                $grid->value('数量')->display(function ($v) {
                    return round($v / 1000000000000000000, 4) . " Ether";
                });

                $grid->status('状态')->display(function ($s) {
                    if ($s == 0) {
                        return '<a href="#">待处理</a>';
                    } else {
                        return '已处理';
                    }
                });

                $grid->timeStamp('交易时间')->display(function ($ts) {
                    return Carbon::createFromTimestamp($ts)->toDateTimeString();
                });

            });

            $grid->model()->where('tokenSymbol', '=', null);
            $grid->paginate(15);
            $grid->perPages([10, 20, 30, 40, 50]);
            $grid->disableCreateButton();
            //$grid->disableActions();
            //$grid->actions(function ($actions) {
            //    $actions->disableDelete();
            //    $actions->disableEdit();
            //});
            $content->body($grid);
        });
    }

    public function getHlwTxList()
    {
        return Admin::content(function (Content $content) {

            $content->header('HLW交易订单');
            //$content->description('description');

            $grid = Admin::grid(Trascation::class, function(Grid $grid) {

                $grid->id('ID')->sortable();

                $grid->column('userId', '用户ID')->display(function () {
                    return '#1111';
                });

                $grid->column('hash', '流水号');

                $grid->column('from', '发送方');

                $grid->column('direction', '去向')->display(function () {
                    /**
                     * @todo 这个地址发布时需要修改
                     */
                    if ($this->from == '0x36292dc34148a30fa50d7381a78a9c173bdfd3ac') {
                        return '<span class="label label-info rounded">OUT</span>';
                    } else {
                        return '<span class="label label-success rounded">&nbsp; IN &nbsp;</span>';
                    }

                });

                $grid->column('to', '接收方');

                $grid->value('数量')->display(function ($v) {
                    return round($v / 10000, 4) . " HLW";
                });

                $grid->status('状态')->display(function ($s) {
                    if ($s == 0) {
                        return '<a href="#">待处理</a>';
                    } else {
                        return '已处理';
                    }
                });

                $grid->timeStamp('交易时间')->display(function ($ts) {
                    return Carbon::createFromTimestamp($ts)->toDateTimeString();
                });
            });

            $grid->model()->where('tokenSymbol', '=', "HLW");
            $grid->paginate(15);
            $grid->perPages([10, 20, 30, 40, 50]);
            $grid->disableCreateButton();
            //$grid->disableActions();
            //$grid->actions(function ($actions) {
            //    $actions->disableDelete();
            //    $actions->disableEdit();
            //});

            $content->body($grid);
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Trascation::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Trascation::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
