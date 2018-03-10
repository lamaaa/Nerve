<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique()->default('')->comment('用户名');
            $table->string('phone')->unique()->default('')->comment('手机号码');
            $table->string('email')->unique()->default('')->comment('邮箱');
            $table->string('password')->default('')->comment('密码哈希');
            $table->smallInteger('status')->default(1)->comment('状态');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
