<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePatientsAddEmergencyContactNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function($column){
            $column->string('pat_emergency_contact_number', 20)->nullable()->comment('Patient emergency contact number'); 
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
            $column->dropColumn('pat_emergency_contact_number'); 
        });
    }
}
