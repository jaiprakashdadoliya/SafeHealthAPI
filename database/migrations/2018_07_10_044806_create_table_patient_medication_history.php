<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientMedicationHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_medication_history')) {
            Schema::create('patient_medication_history', function (Blueprint $table) {
                $table->increments('pmh_id')->unsigned()->comment('Patients patient_medication_history unique id');
                $table->integer('pat_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->integer('visit_id')->unsigned()->index()->comment('Foreign key from patient_visit table');

                $table->integer('medicine_id')->unsigned()->comment('Refer medicine table');
                $table->date('medicine_start_date')->comment('Medicine start date');
                $table->date('medicine_end_date')->nullable()->comment('Medicine end date');

                $table->tinyInteger('medicine_dose')->nullable()->comment('Medicine dose 8:00 AM');
                $table->tinyInteger('medicine_dose2')->nullable()->comment('Medicine dose 12:00 PM');
                $table->tinyInteger('medicine_dose3')->nullable()->comment('Medicine dose 8:00 PM');
                $table->tinyInteger('medicine_dose_unit')->nullable()->comment('Medicine dose unit');
                $table->tinyInteger('medicine_duration')->nullable()->comment('Medicine duration');
                $table->tinyInteger('medicine_duration_unit')->nullable()->comment('Medicine duration unit');
                $table->tinyInteger('medicine_frequency')->nullable()->comment('Medicine frequency');
                $table->tinyInteger('medicine_meal_opt')->nullable()->comment('Medicine meal option');
                $table->tinyInteger('is_discontinued')->default(2)->nullable()->comment('1 for yes, 2 for no');                
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
