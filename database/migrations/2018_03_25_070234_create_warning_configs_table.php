<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarningConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warning_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stock_id')->default(0)->comment('股票ID');
            $table->integer('user_id')->default(0)->comment('用户ID');
            $table->smallInteger('type')->default(0)->comment('预警类型 1为股价 2为涨跌幅');
            $table->integer('value')->default(0)->comment('预警值');
            $table->boolean('switch')->default(false)->comment('开关');
            $table->smallInteger('status')->default(1)->comment('有效性 1为有效 其他为无效');
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
        Schema::dropIfExists('warning_configs');
    }
}
