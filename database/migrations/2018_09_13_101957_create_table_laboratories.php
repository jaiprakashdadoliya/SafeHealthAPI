<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLaboratories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->increments('lab_id')->unsigned()->comment('Laboratory primary id');
            $table->integer('user_id')->unsigned()->comment('Foreign key from users table');
            $table->string('lab_name', 100)->nullable()->comment('Laboratory name');
            $table->string('lab_slug', 100)->nullable()->comment('Laboratory slug');
            $table->string('lab_reg_number', 30)->nullable()->comment('Laboratory registration number');
            $table->string('lab_featured_image', 255)->nullable()->comment('Laboratory feature image');
            $table->text('lab_short_info')->nullable()->comment('Laboratory short description');
            $table->string('lab_phone', 50)->nullable()->comment('Laboratory phone number');
            $table->string('lab_address_line1', 255)->nullable()->comment('Laboratory address line1');
            $table->string('lab_address_line2', 255)->nullable()->comment('Laboratory address line2');
            $table->string('lab_landmark', 255)->nullable()->comment('Laboratory address landmark');
            $table->string('lab_pincode', 10)->nullable()->comment('Laboratory address pin code');
            $table->float('lab_latitude',30)->nullable()->comment('Laboratory address latitude'); 
            $table->float('lab_longitude',30)->nullable()->comment('Laboratory address longitude'); 
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
        Schema::dropIfExists('laboratories');
    }
}
