<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrugTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drug_type', function (Blueprint $table) {
            $table->increments('drug_type_id')->unsigned()->comment('Drug Type Unique ID');
            $table->string('drug_type_name',255)->comment('Drug Type Name');
            $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
            $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
            $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
            $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
            $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
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
        Schema::dropIfExists('drug_type');
    }
}
