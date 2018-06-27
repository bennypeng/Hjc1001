<?php

namespace App\Admin\Controllers;

use App\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Contracts\HelperContract;

class UserController extends Controller
{
    use ModelForm;

    protected $helper;

    public function __construct(HelperContract $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('用户');
            $content->description('用户信息');

            $grid = Admin::grid(User::class, function(Grid $grid) {

                $grid->id('ID')->sortable();

                $grid->mobile('手机号码');

                $grid->nickname('昵称')->editable();

                $grid->icon('头像');

                $grid->hlw_wallet('HLW余额');

                $grid->eth_wallet('ETH余额');

                $grid->address('钱包地址')->display(function ($address) {
                    return $address ? $address : '-';
                })->editable();

                $grid->agent_level('代理等级');

                $grid->invite_code('邀请码')->display(function ($ic) {
                    return $ic ? $ic : '-';
                });

                $grid->invite_id('邀请人ID')->display(function ($uid) {
                    return $uid ? $uid : '-';
                });

                $grid->created_at('创建时间');

                $grid->updated_at('修改时间');

            });

            $grid->model()->orderBy('id', 'desc');
            $grid->paginate(20);
            $grid->perPages([10, 20, 30, 40, 50]);

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

            $content->header('编辑用户信息');
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

            $content->header('创建用户');
            //$content->description('创建游戏用户');

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
        return Admin::grid(User::class, function (Grid $grid) {

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
        return Admin::form(User::class, function (Form $form) {

            $form->display('id', '用户ID');

            $form->text('mobile', '手机号码')->readOnly();

            $form->text('nickname', '昵称');

            $form->password('password', '密码');

            $form->text('address', '钱包地址');

            $directors = [
                '1'  => '一级',
                '2'  => '二级',
                '3'  => '三级',
            ];

            $form->radio('agent_level', '代理等级')->options($directors)->default('3');

            $form->divide();

            $form->text('hlw_wallet', 'HLW余额')->default(0)->readOnly();

            $form->text('eth_wallet', 'ETH余额')->default(0)->readOnly();

            $form->text('hlw_lock_wallet', 'HLW冻结余额')->default(0)->readOnly();

            $form->text('eth_lock_wallet', 'ETH冻结余额')->default(0)->readOnly();

            $form->text('invite_code', '邀请码')->placeholder(' ')->readOnly();

            $form->text('invite_id', '邀请人ID')->placeholder(' ')->readOnly();

            //$form->text('icon', '头像')->default('1')->readOnly();

            $form->display('created_at', '创建时间');

            $form->display('updated_at', '修改时间');

            $form->saving(function(Form $form) {
                if (!$form->password)
                    $form->ignore(['password']);
                if($form->password && $form->model()->password != $form->password)
                {
                    $form->password = bcrypt($form->password);
                }
            });
        });
    }
}