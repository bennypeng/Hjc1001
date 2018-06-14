<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->comment('用户自增ID');
            $table->char('mobile', 20)->comment('手机号码');
            $table->string('nickname', 20)->comment('昵称');
            $table->string('password', 100)->comment('密码');
            $table->integer('wallet')->default(0)->comment('钱包余额');
            $table->char('address',42)->nullable()->comment('钱包地址');
            $table->timestamps();
            $table->unique('mobile');
            $table->index(['mobile', 'password']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
