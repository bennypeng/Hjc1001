<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPetsTable6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pets', function (Blueprint $table) {
            DB::statement("ALTER TABLE `pets` MODIFY COLUMN `attr1` TINYINT(4) NOT NULL DEFAULT '0'");
            DB::statement("ALTER TABLE `pets` MODIFY COLUMN `attr2` TINYINT(4) NOT NULL DEFAULT '0'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pets', function (Blueprint $table) {
            //
        });
    }
}
