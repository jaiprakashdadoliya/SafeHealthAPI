<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInvestigationSleepStudy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('investigation_sleep_study')) {
            Schema::create('investigation_sleep_study', function (Blueprint $table) {
                $table->increments('iss_id')->unsigned()->comment('Investigation sleep study primary ID');
                $table->integer('visit_id')->unsigned()->index()->comment('Visit ID');
                $table->integer('pat_id')->unsigned()->index()->comment('Patient ID');
                $table->string('investigation_ahi', 255)->unsigned()->nullable()->comment('AHI');
                $table->string('investigation_ri', 255)->nullable()->comment('RI');
                $table->text('investigation_conclusion')->nullable()->comment('Investigation Conclusion');
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for yes, 2 for no');
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
        //
    }
}
