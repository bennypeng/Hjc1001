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
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\Config;

class ExtractController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('提现申请管理');
            //$content->description('执行 \'同意\' 操作前必须到通过总账户对指定钱包地址进行成功转账');

            $grid = Admin::grid(Extraction::class, function(Grid $grid) {

                $grid->id('ID')->sortable();

                $grid->column('userid', '用户ID');

                $grid->column('address', '钱包地址');

                $grid->column('money', '金额');

                $grid->flag('提现类型')->display(function ($s) {
                    if ($s == 'hlw')
                        return '<span class="label label-info rounded">' . strtoupper($s) . '</span>';
                    if ($s == 'eth')
                        return '<span class="label label-success rounded">' . strtoupper($s) . '</span>';
                    return '<span class="label label-danger rounded">UNKOWN</span>';
                });

                $grid->status('状态')->display(function ($s) {
                    if ($s == '0') {
                        return '<span class="label label-warning rounded">待处理</span>';
                    } else if ($s == '-1'){
                        return '<span class="label label-default rounded">已撤销</span>';
                    }  else if ($s == '2'){
                        return '<span class="label label-danger rounded">已拒绝</span>';
                    }else {
                        return '<span class="label label-success rounded">已处理</span>';
                    }
                });

                $grid->column('remark', '备注');

                $grid->column('created_at', '申请时间');


            });
            
            $appDomain = env('APP_DOMAIN');
            $this->script = <<<EOT
$('.pass').unbind('click').click(function() {
    var id = $(this).data('id');
    swal({
        title: "确认提现通过?",
        text: "执行此操作前请确保已对该钱包进行转账！", 
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#AEDEF4",
        confirmButtonText: "确认",
        cancelButtonText: "取消",
        showLoaderOnConfirm: true,
        closeOnConfirm: false
    },
    function(){
        $.ajax({
            method: 'POST',
            url: 'http://$appDomain/api/user/opt/1',
            data: {
                "id": id
            },
            success: function (data) {
                $.pjax.reload('#pjax-container');
                if (typeof data === 'object') {
                    if (data.code == 10060) {
                        swal(data.message, '', 'success');
                    } else {
                        swal(data.message, '', 'error');
                    }
                }
            }
        });
    });
});

$('.reject').unbind('click').click(function() {
    var id = $(this).data('id');
    swal({
        title: "拒绝提现？",
        type: "input",
        showCancelButton: true,
        closeOnConfirm: false,
        animation: "slide-from-top",
        inputPlaceholder: "拒绝理由",
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "确认",
        cancelButtonText: "取消",
        closeOnCancel: true,
        showLoaderOnConfirm: true
    },
    function(inputValue){
        if (inputValue === false) return false;

        if (inputValue === "") {
          swal.showInputError("请输入拒绝理由！");
          return false
        }

        $.ajax({
            method: 'POST',
            url: 'http://$appDomain/api/user/opt/2',
            data: {
                "id": id,
                "remark":inputValue
            },
            success: function (data) {
                $.pjax.reload('#pjax-container');
                if (typeof data === 'object') {
                    if (data.code == 10060) {
                        swal(data.message, '', 'success');
                    } else {
                        swal(data.message, '', 'error');
                    }
                }
            }
        });
    });
});
EOT;
            Admin::script($this->script);

            $grid->model()->orderBy('id', 'desc');
            $grid->paginate(15);
            $grid->perPages([10, 20, 30, 40, 50]);
            $grid->disableCreateButton();
            //$grid->disableActions();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->append("<a href='' title='通过' class='pass' data-id='{$actions->getKey()}'><i class='fa fa-check'></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;");
                $actions->append("<a href='' title='拒绝' class='reject' data-id='{$actions->getKey()}' style='color: #ff3c5c;'><i class='fa fa-close'></i></a>");
                //$actions->append("<a href='' style='color: #4f6eff;'><i class='fa fa-info-circle'></i></a>");
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
