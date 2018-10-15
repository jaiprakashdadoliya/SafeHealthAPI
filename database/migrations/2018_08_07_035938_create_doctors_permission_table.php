<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorsPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors_permission', function (Blueprint $table) {
            $table->increments('doc_per_id')->unsigned()->comment('Setting Primary ID');
            $table->integer('visit_cmp_id')->unsigned()->comment('Foreign key of component from visits_components table');
            $table->tinyInteger('is_visible')->unsigned()->index()->default(1)->comment('Component visibility - 1 for Hide, 2  For show');
            $table->string('ip_address',50)->comment('User last login ip'); 
            $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
            $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
            $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
            $table->timestamps();
            $table->rememberToken();  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctors_permission');
    }
}
