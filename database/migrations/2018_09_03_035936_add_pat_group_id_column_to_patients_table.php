<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatGroupIdColumnToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('patients', function($column){
            $column->integer('pat_group_id')->unsigned()->nullable()->comment('Foreign key from patient groups table'); 
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function($column){
            $column->dropColumn('pat_group_id'); 
        });
    }
}
