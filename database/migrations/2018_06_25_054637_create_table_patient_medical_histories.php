<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientMedicalHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_medical_histories')) {
            Schema::create('patient_medical_histories', function (Blueprint $table) {
                $table->increments('pmh_id')->comment('primary key, autoincreament');
                $table->integer('pat_id')->comment('Foreign Key From user table');
                $table->integer('visit_id')->comment('Foreign Key From patients_visits table');
                $table->integer('pmh_disease_id')->comment('Foreign Key From diseases table');
                $table->tinyInteger('is_happend')->nullable()->default(0)->comment('Happend or Not - 1 For Never , 2 for Before ILD Treatment , 3 for After ILD Treatment');
                      
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('pat_id')->references('user_id')->on('users')->onUpdate('cascade');
                $table->foreign('visit_id')->references('visit_id')->on('patients_visits')->onUpdate('cascade');
                $table->foreign('pmh_disease_id')->references('disease_id')->on('diseases')->onUpdate('cascade');
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
