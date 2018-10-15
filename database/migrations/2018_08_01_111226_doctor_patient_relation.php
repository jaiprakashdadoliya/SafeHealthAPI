<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DoctorPatientRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('doctor_patient_relation')) {
            Schema::create('doctor_patient_relation', function (Blueprint $table) {
                $table->increments('rel_id')->unsigned()->comment('Doctor Patients Relation Unique ID');
                $table->integer('doc_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->integer('pat_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->integer('assign_by_doc')->unsigned()->comment('Who assigned this patient');
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
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
        Schema::dropIfExists('doctor_patient_relation');
    }
}
