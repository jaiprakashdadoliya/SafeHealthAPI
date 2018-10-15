<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWorkEnvironment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('work_environment_factor')) {
            Schema::create('work_environment_factor', function (Blueprint $table) {
                $table->increments('wef_id')->unsigned()->comment('Patients work_environment_factor unique id');
                $table->integer('pat_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->integer('visit_id')->unsigned()->index()->comment('Foreign key from patient_visit table');
                
                $table->integer('wef_is_working_location_outside')->nullable()->comment('wef_is_working_location_outside yes no checkbox option value 1 for yes and 2 for no');
                $table->integer('wef_is_smoky_dust')->nullable()->comment('wef_is_smoky_dust yes no checkbox option value 1 for yes and 2 for no');
                $table->integer('wef_use_of_protective_masks')->nullable()->comment('wef_use_of_protective_masks yes no checkbox option value 1 for yes and 2 for no');

                $table->string('wef_occupation',255)->comment('wef_occupation required ');
                $table->integer('wef_worked_from_month')->nullable()->comment('wef_worked_from_month form 1 to 12. 1 for jan ,2 for feb and so on');
                $table->integer('wef_worked_from_year')->nullable()->comment('wef_worked_from_year');
                $table->integer('wef_worked_to_month')->nullable()->comment('wef_worked_to_month form 1 to 12. 1 for jan ,2 for feb and so on');
                $table->integer('wef_worked_to_year')->nullable()->comment('wef_worked_to_year');
                $table->string('wef_exposures',255)->nullable()->comment('wef_exposures');
                
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
        Schema::dropIfExists('work_environment_factor');
    }
}
