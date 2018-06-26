<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extractions', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('userid')->comment('用户ID');
            $table->char('address',42)->nullable()->comment('钱包地址');
            $table->string('money', 30)->default(0)->comment('提现金额');
            $table->string('flag', 30)->nullable()->comment('单位');
            $table->tinyInteger('status')->default(0)->comment('状态');
            $table->timestamps();
            $table->index('userid');
            $table->index('address');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extractions');
    }
}
