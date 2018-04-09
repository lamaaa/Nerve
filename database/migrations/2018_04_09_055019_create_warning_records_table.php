<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarningRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warning_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(0)->comment('用户ID');
            $table->string('stock_code')->default('')->comment('股票代码');
            $table->string('stock_name')->default('')->comment('股票名称');
            $table->string('stock_price')->default('')->comment('股票价格');
            $table->string('stock_quote_change')->default('')->comment('股票涨幅');
            $table->string('notification_types')->default('')->comment('预警方式');
            $table->string('warning_setting')->default('')->comment('预警设置');
            $table->smallInteger('status')->default(1)->comment('状态');
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
        Schema::dropIfExists('warning_records');
    }
}
