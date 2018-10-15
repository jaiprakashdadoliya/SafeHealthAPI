<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVisitDiagnosisAddEndDateOfDiagnosis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients_visit_diagnosis', function($column){
            $column->date('diagnosis_end_date')->nullable()->comment('End date of diagnosis'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients_visit_diagnosis', function($column){
            $column->dropColumn('diagnosis_end_date'); 
        });
    }
}
