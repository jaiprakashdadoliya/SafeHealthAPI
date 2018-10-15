<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientsVisits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patients_visits')) {
            Schema::create('patients_visits', function (Blueprint $table) {
                $table->increments('visit_id')->comment('Visit ID');
                $table->integer('pat_id')->comment('User ID - Foreign Key From users table');
                $table->integer('user_id')->index()->comment('Refer users.user_id');
                $table->tinyInteger('status')->default('1')->comment('Current Status of row - 1 for in progress, 2 finished');
                $table->tinyInteger('visit_type')->comment('1 for initial visit, 2 for followup visit');
                $table->tinyInteger('visit_symptom_status')->unsigned()->nullable()->comment('1 for Improved, 2 for Same, 3 for Deteriorated');
                $table->tinyInteger('visit_followup_status')->unsigned()->nullable()->comment('1 for Yes, 2 for No');
                $table->tinyInteger('visit_followed_elsewhere')->unsigned()->nullable()->comment('1 for Known, 2 for Unknown');
                $table->integer('visit_number')->nullable()->comment('Patient\'s doctor visit number');
                      
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
        Schema::dropIfExists('patients_visits');
    }
}
