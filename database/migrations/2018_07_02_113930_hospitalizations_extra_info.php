<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HospitalizationsExtraInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hospitalizations_extra_info')) {
            Schema::create('hospitalizations_extra_info', function (Blueprint $table) {
                $table->increments('hei_id')->comment('Hospitalization extra information id');
                $table->integer('pat_id')->comment('User ID - Foreign Key From users table');
                $table->integer('visit_id')->comment('Visit ID - Foreign Key From patient visits table');
                $table->integer('hospitalization_fector_id')->comment('Hospitalization fector id');
                $table->string('hospitalization_diagnosis_details', 255)->nullable()->comment('Diagnosis details');                
                $table->date('hospitalization_date')->nullable()->comment('Hospitalization date');
                $table->string('hospitalization_duration', 255)->nullable()->comment('Hospitalization duration');                
                $table->tinyInteger('hospitalization_duration_unit')->unsigned()->nullable()->comment('1 for days, 2 for weeks, 3 for months');                
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('pat_id')->references('user_id')->on('users')->onUpdate('cascade');
                $table->foreign('visit_id')->references('visit_id')->on('patients_visits')->onUpdate('cascade');
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
