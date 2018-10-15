<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFamilyMedicalHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (!Schema::hasTable('family_medical_histories')) {
            Schema::create('family_medical_histories', function (Blueprint $table) {
                $table->increments('fmh_id')->unsigned()->comment('family_medical_histories unique id');
                $table->integer('pat_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->integer('visit_id')->unsigned()->index()->comment('Foreign key from patient_visit table');
                
                $table->integer('fmh_disease_id')->comment('Foreign  key from disease table');
                $table->string('disease_status',255)->nullable()->comment('disease_status yes or no or unspecified message');
                $table->string('family_relation',255)->nullable()->comment('1 for Grandparents, 2 for Parents,3 for  Brothers,4 for Sisters,5 for Aunts, 6 for Uncles, 7  for First Cousins,8  for Children ');
                
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
                $table->foreign('pat_id')->references('user_id')->on('users')->onUpdate('cascade');
                $table->foreign('visit_id')->references('visit_id')->on('patients_visits')->onUpdate('cascade');
                $table->foreign('fmh_disease_id')->references('disease_id')->on('diseases')->onUpdate('cascade');
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
        Schema::dropIfExists('family_medical_histories');
    }
}
