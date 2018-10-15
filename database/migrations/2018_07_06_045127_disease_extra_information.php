<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DiseaseExtraInformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('disease_extra_information')) {
            Schema::create('disease_extra_information', function (Blueprint $table) {
                $table->increments('dei_id')->unsigned()->comment('disease extra info unique id');
                $table->integer('disease_id')->unsigned()->index()->comment('Foreign key from diseases table');
                $table->string('info_type', 50)->nullable()->comment('checkbox, textbox, date etc');
                $table->string('info_title',255)->nullable()->comment('Information Title');
                $table->integer('info_view_in')->nullable()->unsigned()->index()->comment('1 for New Visit form');
                $table->integer('info_order')->nullable()->default(0)->unsigned()->index()->comment('order of fields');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->foreign('disease_id')->references('disease_id')->on('diseases')->onUpdate('cascade');
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
