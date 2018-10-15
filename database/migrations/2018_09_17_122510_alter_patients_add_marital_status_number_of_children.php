<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPatientsAddMaritalStatusNumberOfChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function($column){
            $column->tinyInteger('pat_marital_status')->unsigned()->nullable()->comment('1 for Married, 2 for Unmarried'); 
            $column->string('pat_number_of_children', 10)->nullable()->comment('Number of Children'); 
            $column->string('pat_religion', 255)->nullable()->comment('Religion'); 
            $column->string('pat_informant', 255)->nullable()->comment('Informant'); 
            $column->string('pat_reliability', 255)->nullable()->comment('Reliability'); 
            $column->string('pat_occupation', 255)->nullable()->comment('Occupation'); 
            $column->string('pat_education', 255)->nullable()->comment('Education'); 
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
            $column->dropColumn('pat_marital_status'); 
            $column->dropColumn('pat_number_of_children'); 
            $column->dropColumn('pat_religion'); 
            $column->dropColumn('pat_informant'); 
            $column->dropColumn('pat_reliability'); 
            $column->dropColumn('pat_occupation'); 
            $column->dropColumn('pat_education'); 
        });
    }
}
