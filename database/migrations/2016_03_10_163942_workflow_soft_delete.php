<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class WorkflowSoftDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropUnique('workflow_start_at_author_id_unique');
            $table->unique(['start_at', 'author_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->dropUnique('workflow_start_at_author_id_deleted_at_unique');
            $table->unique(['start_at','author_id']);
        });
    }
}
