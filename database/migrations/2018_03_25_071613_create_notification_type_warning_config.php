<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTypeWarningConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_type_warning_config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('notification_type_id')->default(0)->comment('通知类型ID');
            $table->integer('warning_config_id')->default(0)->comment('预警配置ID');
            $table->smallInteger('status')->default(1)->comment('有效性');
            $table->timestamps();

            $table->index('notification_type_id');
            $table->index('warning_config_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_type_warning_config');
    }
}
