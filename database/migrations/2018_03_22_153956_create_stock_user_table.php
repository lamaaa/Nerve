<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(0)->comment('用户ID');
            $table->integer('stock_id')->default(0)->comment('股票ID');
            $table->smallInteger('status')->default(1)->comment('状态');
            $table->timestamps();

            $table->index('user_id');
            $table->index('stock_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_user');
    }
}
