<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WorkflowCommentNullable extends Migration
{
    public function __construct()
    {
        //fix doctrine
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('workflow', function(Blueprint $table) {
          $table->mediumText('comment')->nullable(true)->change();
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
          $table->mediumText('comment')->nullable(false)->change();
      });
    }
}
