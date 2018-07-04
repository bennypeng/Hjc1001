<?php

namespace App\Admin\Controllers;

use App\Pet;

use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Services\HelperService;
use Illuminate\Support\MessageBag;

class PetController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('宠物');
            $content->description('宠物信息');
            $grid = Admin::grid(Pet::class, function(Grid $grid) {

                $grid->id('ID')->sortable();

                $grid->ownerId('主人ID')->display(function ($ownerId) {
                    if ($ownerId == 0)
                        return '-';
                    return $ownerId;
                })->sortable();

                $grid->type('类型');

                $grid->attr1('体力等级')->sortable();

                $grid->attr2('属性等级')->sortable();

                $grid->attr3('装饰完整度')->display(function ($attr3) {
                    $helper = new HelperService();
                    return implode(', ', $helper->parseNum2Bit($attr3));
                });

                $grid->attr4('随机属性值');

                $grid->matchId('当前比赛ID');

                $grid->on_sale('拍卖状态')->display(function ($onsale) {
                    if (time() > strtotime($this->expired_at)) {
                        return '已过期';
                    } else {
                        if ($onsale == 2) {
                            return '拍卖中';
                        } else {
                            return '已下架';
                        }
                    }
                })->sortable();

                $grid->column('petStatus', '宠物状态')->display(function () {
                    if ($this->ownerId == 0 && time() > strtotime($this->expired_at)) {
                        return '已失效';
                    } else {
                        return '成长中';
                    }
                });

                $grid->sp('起始价格');

                $grid->fp('终止价格');

                $grid->price('成交价格')->display(function ($p) {
                    return $p ? $p : '-';
                })->sortable();

                $grid->expired_at('拍卖过期时间')->sortable();

                $grid->created_at('出生时间')->sortable();

                //  搜索框设置
                $grid->filter(function (Grid\Filter $filter) {
                    $filter->equal('ownerId', '主人ID');
                });

            });

            Admin::script($this->script());

            $grid->model()->orderBy('ownerId', 'desc');
            $grid->paginate(20);
            $grid->perPages([10, 20, 30, 40, 50]);
            $grid->disableCreateButton();
            //$grid->disableActions();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                //$actions->disableEdit();
                $actions->prepend("<a href='' title='赠送宠物' class='sentto' data-id='{$actions->getKey()}'><i class='fa fa-rocket'></i></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;");
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

            $content->header('编辑宠物信息');
            //$content->description('description');

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
        return Admin::grid(Pet::class, function (Grid $grid) {

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
        return Admin::form(Pet::class, function (Form $form) {

            $form->display('id', '宠物ID');

            $form->text('ownerId', '主人ID')
                ->prepend('<i class="fa fa-ban fa-fw"></i>')
                ->readOnly();

            $level = [0 => '0级', 1 => '1级', 2 => '2级', 3 => '3级', 4 => '4级', 5 => '5级', 6 => '6级'];

            $form->select('attr1', '体力等级')->options($level);

            $form->select('attr2', '属性等级')->options($level);

            $form->text('attr3', '装饰度')
                ->prepend('<i class="fa fa-ban fa-fw"></i>')
                ->readOnly();

            $form->text('attr4', '随机加成')
                ->prepend('<i class="fa fa-ban fa-fw"></i>')
                ->readOnly();

            $form->divide();

            $form->radio('on_sale', '拍卖状态')->options(['1' => '下架', '2'=> '上架'])->default(1);

            $form->text('sp', '起始价格');

            $form->text('fp', '终止价格');

            $form->text('price', '成交价')
                ->prepend('<i class="fa fa-ban fa-fw"></i>')
                ->default('-')
                ->readOnly();

            $form->text('matchId', '当前比赛ID')
                ->prepend('<i class="fa fa-ban fa-fw"></i>')
                ->default(0)
                ->readOnly();

            $form->display('expired_at', '下架时间');

            $form->display('created_at', '出生时间');

            $form->display('updated_at', '修改时间');

            //  保存前面回调
            $form->saving(function(Form $form) {
                if ($form->model()->ownerId == 0) {
                    //  禁止修改系统宠物
                    $error = new MessageBag([
                        'title'   => '出错啦',
                        'message' => '系统宠物禁止修改....',
                    ]);
                    return back()->with(compact('error'));
                }
            });

            //  保存后回调
            $form->saved(function (Form $form) {
                //  清除宠物缓存
                $helper = new HelperService();
                $helper->delPetInfo($form->model()->id);
            });

            Admin::script($this->script());
        });
    }

    protected function script()
    {
        $appDomain = env('APP_DOMAIN');
        return <<<SCRIPT
$('.sentto').unbind('click').click(function() {
    var id = $(this).data('id');
    swal({
        title: "赠送宠物",
        inputPlaceholder: "用户ID",
        type: "input",
        showCancelButton: true,
        confirmButtonColor: "#AEDEF4",
        confirmButtonText: "确认",
        cancelButtonText: "取消",
        showLoaderOnConfirm: true,
        closeOnConfirm: false,
        closeOnCancel:true
    },
    function(inputValue){
        if (inputValue === false) return false; 
        if (inputValue === "") { 
            swal.showInputError("请输入用户ID");
            return false
        } 
        $.ajax({
            method: 'POST',
            url: 'http://$appDomain/api/pet/send',
            data: {
                "petid": id,
                "userid": inputValue
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