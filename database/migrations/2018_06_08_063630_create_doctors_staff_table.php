<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorsStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors_staff', function (Blueprint $table) {
            $table->increments('doc_staff_id')->comment('Doctors Staff ID');
            $table->Integer('user_id')->comment('Foreign Key From user table');
            $table->string('doc_staff_name')->comment('Name of Staff Person');
            $table->tinyInteger('doc_staff_gender')->nullable()->comment('Gender of Staff Person -  1 for Male, 2 for Female, 3 for Transgender');
            $table->string('doc_staff_mobile', 15)->comment('Mobile Number of Staff Person');
            $table->tinyInteger('doc_staff_role')->default('1')->comment('Role of the Staff Person');
            $table->string('doc_staff_profile_image', 100)->nullable()->comment('Profile Image of Staff Person');
            $table->integer('created_by')->comment('User who created staff person');
            $table->integer('updated_by')->comment('User who updated staff details');
            $table->tinyInteger('resource_type')->default('1')->comment('Resource type - 1 For Web, 2 for Android and 3 for IOS');
            $table->string('ip_address', 20)->comment('ip address of the device');
            $table->tinyInteger('is_deleted')->nullable()->default(2)->comment('Staff delete - 1 For yes, 2 for no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctors_staff');
    }
}
