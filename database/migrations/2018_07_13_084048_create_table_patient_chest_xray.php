<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientChestXray extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_chest_xray')) {
            Schema::create('patient_chest_xray', function (Blueprint $table) {
                $table->increments('pcx_id')->comment('Primary key patient_chest_xray table');
                $table->integer('pat_id')->index()->comment('User ID - Foreign Key From users table');
                $table->integer('visit_id')->index()->comment('Visit ID - Foreign Key From patient visits table');
                $table->tinyInteger('pcx_type')->unsigned()->nullable()->comment('1 for yes, 2 for no');
                $table->date('pcx_date')->nullable()->comment('Date of chest xray');
                $table->tinyInteger('pcx_bilateral_shadows_present')->unsigned()->nullable()->comment('1 for yes, 2 for no');

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
         Schema::dropIfExists('patient_chest_xray');
    }
}
