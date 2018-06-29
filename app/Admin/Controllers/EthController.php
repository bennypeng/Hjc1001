<?php

namespace App\Admin\Controllers;

use App\Trascation;
use App\User;

use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Services\HelperService;
use Illuminate\Support\Facades\Config;

class EthController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('ETH充值订单');
            //$content->description('description');

            $grid = Admin::grid(Trascation::class, function(Grid $grid) {

                $grid->id('ID')->sortable();

                $grid->column('userId', '用户ID')->display(function () {
                    $helper = new HelperService();
                    $userId = $helper->getAddressUserId($this->from);
                    return $userId ? $userId : '-';
                });

                $grid->column('hash', '流水号');

                $grid->column('from', '发送方');

                $grid->column('direction', '去向')->display(function () {
                    return '<span class="label label-success rounded">&nbsp; 充值 &nbsp;</span>';
                });

                $grid->column('to', '接收方');

                $grid->value('数量')->display(function ($v) {
                    return round($v / 1000000000000000000, 4) . " Ether";
                });

                $grid->status('状态')->display(function ($s) {
                    $helper = new HelperService();
                    $userId = $helper->getAddressUserId($this->from);
                    if (!$userId)
                        return '-';
                    if ($s == 0) {
                        return '<span class="label label-warning rounded">待处理</span>';
                    } else if ($s == -1) {
                        return '<span class="label label-danger rounded">拒绝</span>';
                    } else if ($s == 1) {
                        return '<span class="label label-success rounded">已处理</span>';
                    } else {
                        return '<span class="label label-danger rounded">未知状态</span>';
                    }
                });

                $grid->timeStamp('交易时间')->display(function ($ts) {
                    return Carbon::createFromTimestamp($ts)->toDateTimeString();
                });

            });

            $ethAddr = Config::get('constants.ETH_ADDR');

            Admin::script($this->script());

            $grid->model()->where('tokenSymbol', '=', null)
                ->where('from', '!=', $ethAddr)
                ->where('to', '=', $ethAddr);
            $grid->paginate(15);
            $grid->perPages([10, 20, 30, 40, 50]);
            $grid->disableCreateButton();
            //$grid->disableActions();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->append("<a href='' title='下发积分' class='pass' data-id='{$actions->getKey()}'><i class='fa fa-check'></i></a>");
                //$actions->append("<a href='' title='通过' class='pass' data-id='{$actions->getKey()}'><i class='fa fa-check'></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;");
                //$actions->append("<a href='' title='拒绝' class='reject' data-id='{$actions->getKey()}' style='color: #ff3c5c;'><i class='fa fa-close'></i></a>");
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

    protected function script()
    {
        $appDomain = env('APP_DOMAIN');
        return <<<SCRIPT
$('.pass').unbind('click').click(function() {
    var id = $(this).data('id');
    swal({
        title: "确认下发积分?",
        text: "执行此操作前请确保该订单已交易成功！", 
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
            url: 'http://$appDomain/api/user/send',
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
SCRIPT;
    }

}
