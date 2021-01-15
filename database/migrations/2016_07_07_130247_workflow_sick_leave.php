<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WorkflowSickLeave extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE workflow MODIFY COLUMN type ENUM('working_off', 'time_off', 'vacation', 'sick_leave')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("ALTER TABLE workflow MODIFY COLUMN type ENUM('working_off', 'time_off', 'vacation')");
    }
}
