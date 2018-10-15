<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('srv_id')->comment('Service Primary ID');
            $table->smallInteger('user_id')->unsigned()->comment('Foreign Key From users table');
            $table->string('srv_name', 150)->comment('Service Name');
            $table->integer('srv_cost')->unsigned()->comment('Service cost');
            $table->integer('srv_duration')->unsigned()->comment('Service duration');
            $table->integer('srv_unit')->unsigned()->comment('Service unit');
            $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
            $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
            $table->string('ip_address')->comment('User last login ip');
            $table->tinyInteger('resource_type')->unsigned()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
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
        Schema::dropIfExists('services');
    }

}
