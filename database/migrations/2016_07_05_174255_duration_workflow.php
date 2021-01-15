<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DurationWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow', function (Blueprint $table) {
            $table->integer('duration')->default(480);
        });
        \DB::statement("UPDATE workflow SET type = 'working_off', duration = 0 WHERE type = 'chase';");
        \DB::statement("ALTER TABLE workflow MODIFY COLUMN type ENUM('working_off', 'time_off', 'vacation')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("ALTER TABLE workflow MODIFY COLUMN type ENUM('working_off', 'time_off', 'vacation', 'chase')");
        \DB::statement("UPDATE workflow SET type = 'chase' WHERE type = 'working_off' AND duration = 0;");
        Schema::table('workflow', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
}
