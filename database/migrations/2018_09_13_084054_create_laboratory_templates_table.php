<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaboratoryTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratory_templates', function (Blueprint $table) {
           $table->increments('lab_temp_id')->comment('Laboratory template autoincrement id');
            $table->integer('user_id')->unsigned()->nullable()->comment('doctor id');
            $table->string('temp_name')->nullable()->comment('Template name');
            $table->json('symptoms_data')->nullable()->comment('Symptoms for this templates');
            $table->json('diagnosis_data')->nullable()->comment('Diagnosis data for this templates');
            $table->json('laboratory_test_data')->nullable()->comment('Laboratory test for this templates');
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
        Schema::dropIfExists('laboratory_templates');
    }
}
