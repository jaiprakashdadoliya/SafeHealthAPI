<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->increments('pat_id')->unsigned()->comment('Patients Unique ID');
                $table->integer('user_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->string('pat_code','8')->nullable()->comment('Patients Code');
                $table->smallInteger('pat_title')->nullable()->comment('1 for Mr, 2 for Ms, 3 for Mrs, 4 for Dr, 5 for Master');
                $table->integer('pat_blood_group')->nullable()->comment('Patients Blood Group');
                $table->string('pat_phone_num',20)->nullable()->comment('Patients contact number 2');
                $table->date('pat_dob')->nullable()->comment('Patients Date of Birth');
                $table->string('pat_address_line1',255)->comment('Patients Locality');
                $table->string('pat_address_line2',255)->nullable()->comment('Patients Locality');
                $table->string('pat_locality',255)->nullable()->comment('Patients Locality');
                $table->integer('city_id')->index()->nullable()->comment('Patients City Id');
                $table->string('pat_other_city',255)->nullable()->comment('Patients select Other city');
                $table->integer('state_id')->index()->nullable()->comment('Patients State Id');
                $table->string('pat_pincode',10)->index()->nullable()->comment('Patients Pincode');
                $table->smallInteger('pat_status')->nullable()->comment('Patients Status - 1 For active , 0 for inactive');
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
                $table->foreign('user_id')->references('user_id')->on('users')->onUpdate('cascade');
                $table->foreign('state_id')->references('id')->on('states')->onUpdate('cascade');
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
        Schema::dropIfExists('patients');
    }
}
