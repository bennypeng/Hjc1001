<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Encore\Admin\Form::forget(['map', 'editor']);
use Encore\Admin\Facades\Admin;

Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {

    //  参考http://laravel-admin.org/docs/#/zh/custom-navbar?id=%E5%B7%A6%E4%BE%A7%E6%B7%BB%E5%8A%A0%E7%A4%BA%E4%BE%8B

    //$navbar->left(view('search-bar'));                            //  左侧搜索框

    //$navbar->right(new \App\Admin\Extensions\Nav\Links());          //  右侧图标/下拉框
});