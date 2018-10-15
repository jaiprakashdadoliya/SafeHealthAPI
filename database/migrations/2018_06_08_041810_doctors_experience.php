<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DoctorsExperience extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */  
    public function up()
    {
        if (!Schema::hasTable('doctors_experience')) {
            Schema::create('doctors_experience', function (Blueprint $table) {
                $table->increments('doc_exp_id')->comment('Primary Key of Doctor Experience table');
                $table->integer('user_id')->unsigned()->index()->comment('Foreign Key from user table');
                $table->string('doc_exp_organisation_name', 100)->comment('Organisation name of experience');
                $table->string('doc_exp_designation', 50)->nullable()->comment('Designation in the Organisation');
                $table->tinyInteger('doc_exp_start_year')->unsigned()->nullable()->comment('Start year of experience');
                $table->tinyInteger('doc_exp_start_month')->unsigned()->nullable()->comment('Start month of experience');
                $table->tinyInteger('doc_exp_end_year')->unsigned()->nullable()->comment('End year of experience');
                $table->tinyInteger('doc_exp_end_month')->unsigned()->nullable()->comment('End month of experience');
                $table->tinyInteger('doc_exp_organisation_type')->unsigned()->default(1)->comment('Organisation Type 1 for goverment, 2 for private');
                $table->tinyInteger('user_type')->unsigned()->default(1)->comment('User Types : 2 for doctors, 3 for patients');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('user_id')->references('user_id')->on('users')->onUpdate('cascade');
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
         Schema::dropIfExists('doctors_experience');
    }
}
