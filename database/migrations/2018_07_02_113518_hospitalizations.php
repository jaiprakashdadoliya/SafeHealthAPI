<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Hospitalizations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hospitalizations')) {
            Schema::create('hospitalizations', function (Blueprint $table) {
                $table->increments('hospitalization_id')->comment('Hospitalization ID');
                $table->integer('pat_id')->comment('User ID - Foreign Key From users table');
                $table->integer('visit_id')->comment('Visit ID - Foreign Key From patient visits table');
                $table->tinyInteger('hospitalization_status')->nullable()->comment('1 for Yes, 2 for No');
                $table->string('hospitalization_how_many', 255)->nullable()->comment('How many details');                
                $table->string('hospitalization_why', 255)->nullable()->comment('Hospitalization Why?');                
                $table->tinyInteger('hospitalization_respiratory')->unsigned()->nullable()->comment('Respiratory id');
                $table->string('hostpitalization_cardiac_myocardial_infarction', 255)->nullable()->comment('Description of CARDIAC: Myocardial infarction');                
                $table->date('date_of_hospitalization')->nullable()->comment('Date of hospitalization');                
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
