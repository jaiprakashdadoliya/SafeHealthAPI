<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManageCaledarSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manage_caledar_setting', function (Blueprint $table) {
            $table->increments('mcs_id');
            $table->string('mcs_slot_duration',4)->nullable()->comment('mcs_slot_duration for calendar setting');
            $table->string('mcs_start_time',4)->nullable()->comment('mcs_start_time for calendar settingp');
            $table->string('mcs_end_time',4)->nullable()->comment('mcs_end_time for calendar setting');
            $table->integer('user_id')->unsigned()->nullable()->comment('Foreign Key from users table');
            $table->timestamps();
            $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
            $table->string('ip_address',50)->nullable()->comment('User last login ip');
            $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
            $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
            $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manage_caledar_setting');
    }
}
