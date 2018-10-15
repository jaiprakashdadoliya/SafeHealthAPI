<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDoctorMedicinesRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_medicines_relation', function (Blueprint $table) {
            $table->increments('dmr_id')->unsigned()->comment('Drug Dose Unit Unique ID');
            $table->integer('medicine_id')->comment('medicine_id foreign key for medicine table');
            $table->integer('user_id')->nullable()->comment('user_id foreign key for users table');
            $table->json('medicine_instractions')->nullable()->comment('genreal medicine instractions');
            $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
            $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
            $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
            $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
            $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
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
        Schema::dropIfExists('doctor_medicines_relation');
    }
}
