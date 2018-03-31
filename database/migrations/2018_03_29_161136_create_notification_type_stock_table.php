<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTypeStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_type_stock', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stock_id')->default(0)->comment('股票ID');
            $table->integer('notification_type_id')->default(0)->comment('预警类型ID');
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
        Schema::dropIfExists('notification_type_stock');
    }
}
