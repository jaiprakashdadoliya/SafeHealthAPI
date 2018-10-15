<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientAllergiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_allergies')) {
            Schema::create('patient_allergies', function (Blueprint $table) {
                $table->increments('pat_alg_id')->comment('primary key, autoincreament');
                $table->integer('pat_id')->comment('Foreign Key From user table');
                $table->integer('allergy_type')->comment('Type of allergy found for patient');
                $table->string('onset',100)->comment('onset for patient');
                $table->tinyInteger('onset_time')->unsigned()->nullable()->comment('1 for days, 2 for weeks, 3 for months, 4 for years');
                $table->tinyInteger('status')->nullable()->default(1)->comment('1 for active, 2 for inactive');
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('pat_id')->references('user_id')->on('users')->onUpdate('cascade');
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
        Schema::dropIfExists('patient_allergies');
    }
}
