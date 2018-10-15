<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePastMedicationHistoryAddDiseaseEndDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_past_medication_history', function($column){
            $column->date('disease_end_date')->nullable()->comment('Disease end date'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_past_medication_history', function($column){
            $column->dropColumn('disease_end_date'); 
        });
    }
}
