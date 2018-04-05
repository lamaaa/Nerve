<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumberOfWarningsToWarningConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warning_configs', function (Blueprint $table) {
            $table->smallInteger('number_of_warnings')->default(0)->comment('当天预警次数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warning_configs', function (Blueprint $table) {
            $table->dropColumn('number_of_warnings');
        });
    }
}
