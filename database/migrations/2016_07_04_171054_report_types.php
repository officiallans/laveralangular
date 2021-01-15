<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReportTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE reports MODIFY COLUMN type ENUM('planned', 'in_progress', 'solved', 'closed')");
        Schema::table('reports', function (Blueprint $table) {
            $table->boolean('archived');
            $table->integer('revision_id')->unsigned()->nullable();
            $table->foreign('revision_id', 'revision_id_reports')->references('id')->on('reports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("ALTER TABLE reports MODIFY COLUMN type ENUM('planned', 'closed', 'solved')");
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('archived');
            $table->dropForeign('revision_id_reports');
            $table->dropColumn('revision_id');
        });
    }
}
