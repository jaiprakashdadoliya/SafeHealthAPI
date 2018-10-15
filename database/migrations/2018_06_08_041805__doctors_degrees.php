<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DoctorsDegrees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('doctors_degrees')) {
            Schema::create('doctors_degrees', function (Blueprint $table) {
                $table->increments('doc_deg_id')->comment('Doctors Degree ID');
                $table->integer('user_id')->unsigned()->index()->comment('Foreign Key from user table');
                $table->integer('user_type')->nullable()->default(2)->comment('User Types : 2 for doctors, 3 for patients');
                $table->string('doc_deg_name', 150)->comment('Degree Name');
                $table->smallInteger('doc_deg_passing_year')->nullable()->comment('Passing year of degree');
                $table->string('doc_deg_institute', 150)->nullable()->comment('Degree Name');
                $table->tinyInteger('resource_type')->unsigned()->default(1)->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->smallInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for yes, 2 for no');
                $table->integer('created_by')->unsigned()->nullable()->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->comment('0 for self/by system');
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
        Schema::dropIfExists('doctors_degrees');
    }
}
