<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClinicsTable extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinics', function (Blueprint $table) {
                $table->increments('clinic_id')->unsigned()->comment('Clinic primary id');
                $table->integer('user_id')->unsigned()->comment('Foreign key from users table');
                $table->string('clinic_name', 255)->nullable()->comment('clinic name');
                $table->string('clinic_phone', 50)->nullable()->comment('clinic phone number');
                $table->string('clinic_address_line1', 255)->nullable()->comment('clinic address line1');
                $table->string('clinic_address_line2', 255)->nullable()->comment('clinic address line2');
                $table->string('clinic_landmark', 255)->nullable()->comment('clinic address landmark');
                $table->string('clinic_pincode', 10)->nullable()->comment('clinic address pin code');
                $table->string('ip_address')->nullable()->comment('last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->comment('Record created by');
                $table->integer('updated_by')->unsigned()->nullable()->comment('Record updated by');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for yes, 2 for no'); 
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
        Schema::dropIfExists('clinics');
    }
}
