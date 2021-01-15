<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveProjects extends Migration
{
    public function up()
    {
        Schema::drop('projects');
    }

    public function down()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->enum('type', ['dataentry', 'freelance', 'project']);
            $table->integer('author_id')->unsigned();
            $table->foreign('author_id', 'author_id_projects')->references('id')->on('users');
            $table->timestamps();
        });
    }
}
