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
            $table->integer('ownerId')->comment('主人ID');
            $table->tinyInteger('type')->comment('宠物类型');
            $table->tinyInteger('attr1')->default(1)->comment('属性1级别');
            $table->tinyInteger('attr2')->default(1)->comment('属性2级别');
            $table->tinyInteger('attr3')->default(0)->comment('属性3');
            $table->timestamps();
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
