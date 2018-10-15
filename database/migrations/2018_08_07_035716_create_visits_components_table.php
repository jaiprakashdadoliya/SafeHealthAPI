<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitsComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits_components', function (Blueprint $table) {
            $table->increments('visit_cmp_id')->unsigned()->comment('Setting Primary ID');
            $table->string('component_title')->index()->nullable()->comment('title of the component');
            $table->string('component_container_name')->index()->nullable()->comment('name of the component in code');
            $table->string('ip_address',50)->comment('User last login ip'); 
            $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
            $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
            $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
            $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
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
        Schema::dropIfExists('visits_components');
    }
}
