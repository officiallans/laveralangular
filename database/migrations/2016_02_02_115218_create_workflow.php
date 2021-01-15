<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflow extends Migration
{
    public function up()
    {
        Schema::create('workflow', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->enum('type', ['working_off', 'time_off', 'vacation']);
            $table->integer('author_id')->unsigned();
            $table->foreign('author_id', 'author_id_workflow')->references('id')->on('users');
            $table->timestamps();
            $table->date('start_at');
            $table->date('end_at')->nullable();
        });
    }

    public function down()
    {
        Schema::drop('workflow');
    }
}
