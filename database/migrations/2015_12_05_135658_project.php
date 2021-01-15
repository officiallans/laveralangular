<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Project extends Migration
{
    public function up()
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

    public function down()
    {
        Schema::drop('projects');
    }
}
