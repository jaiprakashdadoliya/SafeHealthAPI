<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPatientActivityAddVisitId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_activity', function($column){
            $column->integer('visit_id')->after('user_id')->unsigned()->nullable()->comment('Visit Id'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_activity', function (Blueprint $table) {
            $table->dropColumn('visit_id'); 
        });
    }
}
