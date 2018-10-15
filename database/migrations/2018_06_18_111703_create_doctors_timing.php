<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorsTiming extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (!Schema::hasTable('timing')) {
            Schema::create('timing', function (Blueprint $table) {
                $table->increments('timing_id')->comment('Primary Key of Doctor timing table');
                $table->integer('user_id')->unsigned()->comment('Foreign Key from user table');
                $table->tinyInteger('week_day')->unsigned()->comment('Weekday of timing 1 for monday, 2 for tuesday and so on till 7 for sunday ');
                $table->string('start_time')->comment('Start time of shift');
                $table->string('end_time')->comment('End time of shift');
                $table->integer('clinic_id')->unsigned()->comment('Id of clinic from clinic table');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
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
         if (Schema::hasTable('timing')) {
            Schema::dropIfExists('timing');
        }

    }
}
