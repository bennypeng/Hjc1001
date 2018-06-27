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

                $grid->ownerId('主人ID')->sortable();;

                $grid->type('类型');

                $grid->attr1('体力等级')->sortable();

                $grid->attr2('属性等级')->sortable();

                $grid->attr3('装饰完整度')->display(function ($attr3) {
                    $helper = new HelperService();
                    return implode(',', $helper->parseNum2Bit($attr3));
                    //return $attr3;
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

                //$grid->column('petCurPrice', '当前价格')->display(function () {
                //dd($this->helper->getPetInfo($this->id));
                //});

                $grid->sp('起始价格');

                $grid->fp('终止价格');

                $grid->expired_at('拍卖过期时间')->sortable();

                $grid->created_at('出生时间')->sortable();

            });

            $grid->model()->orderBy('ownerId', 'desc');
            $grid->paginate(15);
            $grid->perPages([10, 20, 30, 40, 50]);
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
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

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}