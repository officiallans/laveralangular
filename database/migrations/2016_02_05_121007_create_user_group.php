<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->timestamps();
            $table->integer('author_id')->unsigned();
            $table->foreign('author_id', 'author_id_user_group')->references('id')->on('users');
        });
        Schema::create('user_in_group', function (Blueprint $table) {
            $table->integer('group_id')->unsigned();
            $table->foreign('group_id', 'group_id_in_group')->references('id')->on('user_groups');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id', 'user_id_in_group')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_in_group');
        Schema::drop('user_groups');
    }
}
