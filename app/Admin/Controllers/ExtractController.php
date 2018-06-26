<?php

namespace App\Admin\Controllers;

use App\Trascation;
use App\User;
use App\Extraction;

use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Services\HelperService;

class ExtractController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('提现申请');
            //$content->description('description');

            $grid = Admin::grid(Extraction::class, function(Grid $grid) {

                $grid->id('ID')->sortable();

                $grid->column('userid', '用户ID');

                $grid->column('address', '钱包地址');

                $grid->column('money', '金额');

                $grid->flag('提现类型')->display(function ($s) {
                    if ($s == 'hlw') {
                        return '<span class="label label-info rounded">' . strtoupper($s) . '</span>';
                    } else {
                        return '<span class="label label-success rounded">' . strtoupper($s) . '</span>';
                    }
                });

                $grid->status('状态')->display(function ($s) {
                    if ($s == '0') {
                        return '<span class="label label-warning rounded">等待处理</span>';
                    } else if ($s == '-1'){
                        return '<span class="label label-default rounded">已撤销</span>';
                    } else {
                        return '<span class="label label-success rounded">已处理</span>';
                    }
                });

                $grid->column('created_at', '申请时间');


            });

            $grid->paginate(15);
            $grid->perPages([10, 20, 30, 40, 50]);
            $grid->disableCreateButton();
            //$grid->disableActions();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->append('<a href=""><i class="fa fa-check"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;');
                $actions->append('<a href="" style="color: #ff3c5c;"><i class="fa fa-close"></i></a>');
            });
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
