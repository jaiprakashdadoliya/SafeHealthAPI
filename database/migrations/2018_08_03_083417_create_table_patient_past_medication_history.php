<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientPastMedicationHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_past_medication_history')) {
            Schema::create('patient_past_medication_history', function (Blueprint $table) {
                $table->increments('ppmh_id')->unsigned()->comment('Patients past medication_history unique id');
                $table->integer('pat_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->integer('visit_id')->unsigned()->index()->comment('Foreign key from patient_visit table');

                $table->integer('disease_id')->unsigned()->comment('Refer disease table');
                $table->string('disease_onset',5)->nullable()->comment('disease start');
                $table->tinyInteger('disease_status')->default(1)->nullable()->comment('1 for active, 2 for inactive');
                $table->smallInteger('disease_duration')->unsigned()->comment('Disease duration');
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
                $table->foreign('pat_id')->references('user_id')->on('users')->onUpdate('cascade');
                $table->foreign('visit_id')->references('visit_id')->on('patients_visits')->onUpdate('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
