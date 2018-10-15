<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_activity', function (Blueprint $table) {
            $table->increments('pat_act_id')->comment('Patient activity table autoincrement id');
            $table->integer('user_id')->unsigned()->comment('Doctor id');
            $table->integer('pat_id')->unsigned()->nullable()->comment('Patient id');
            $table->string('activity_table')->nullable()->comment('Activity table name');
            $table->string('ip_address')->nullable()->comment('last login ip'); 
            $table->tinyInteger('resource_type')->unsigned()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
            $table->integer('created_by')->unsigned()->comment('Record created by');
            $table->integer('updated_by')->unsigned()->nullable()->comment('Record updated by');
            $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for yes, 2 for no'); 
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_activity');
    }
}
