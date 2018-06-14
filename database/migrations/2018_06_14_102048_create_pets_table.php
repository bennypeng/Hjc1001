<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->increments('id')->comment('宠物自增ID');
            $table->integer('ownerId')->default(0)->comment('主人ID');
            $table->tinyInteger('type')->comment('宠物类型');
            $table->tinyInteger('attr1')->default(0)->comment('属性1级别');
            $table->tinyInteger('attr2')->default(0)->comment('属性2级别');
            $table->tinyInteger('attr3')->default(0)->comment('属性3');
            $table->tinyInteger('on_sale')->default(1)->comment('上架状态|1已下架|2上架');
            $table->decimal('price', 7, 4)->default(0)->comment('出售价格');
            $table->timestamp('expired_at')->default('1970-01-01 08:00:01')->comment('过期时间');
            $table->timestamps();

            $table->index(['ownerId', 'type']);
            $table->index(['ownerId', 'on_sale']);
            $table->index('expired_at');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pets');
    }
}
