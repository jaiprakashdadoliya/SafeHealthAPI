<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAllergiesHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('allergies_history')) {
            Schema::create('allergies_history', function (Blueprint $table) {
                $table->increments('allergies_history_id')->unsigned()->comment('Allergies history unique id');
                $table->integer('pat_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->integer('visit_id')->unsigned()->index()->comment('Foreign key from patient_visit table');
                $table->integer('allergies_history_type_id')->comment('Allergies history type id from static config data');
                $table->string('allergies_history_value',255)->nullable()->comment('Allergies history value ');
                $table->integer('user_id')->comment('loggedin user id');
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
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
        Schema::dropIfExists('allergies_history');
    }
}
