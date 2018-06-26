<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrascationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trascations', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('blockNumber')->nullable();
            $table->string('blockHash', 300)->nullable();
            $table->integer('timeStamp')->nullable();
            $table->string('hash', 300)->nullable();
            $table->string('nonce', 300)->nullable();
            $table->integer('transactionIndex')->nullable();
            $table->string('from', 300)->nullable();
            $table->string('to', 300)->nullable();
            $table->string('value', 300)->nullable();
            $table->bigInteger('gas')->nullable();

            $table->string('gasPrice', 300)->nullable();
            $table->string('input', 300)->nullable();
            $table->string('contractAddress', 300)->nullable();
            $table->string('cumulativeGasUsed', 300)->nullable();

            $table->tinyInteger('txreceipt_status')->nullable();
            $table->bigInteger('gasUsed')->nullable();
            $table->string('confirmations', 300)->nullable();
            $table->tinyInteger('isError')->nullable();

            $table->string('tokenName', 300)->nullable();
            $table->string('tokenSymbol', 300)->nullable();
            $table->tinyInteger('tokenDecimal')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trascations');
    }
}
