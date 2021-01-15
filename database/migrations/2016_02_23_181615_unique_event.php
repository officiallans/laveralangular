<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UniqueEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow', function(Blueprint $table) {
           $table->unique(['start_at','author_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow', function(Blueprint $table) {
            $table->dropUnique('workflow_start_at_author_id_unique');
        });
    }
}
