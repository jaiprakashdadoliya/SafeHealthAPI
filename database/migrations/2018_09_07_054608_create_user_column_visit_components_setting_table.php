<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserColumnVisitComponentsSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visits_components_settings', function($column){
            $column->integer('user_id')->unsigned()->comment('Foreign key from users table'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('visits_components_settings', function (Blueprint $table) {
            $table->dropColumn('user_id'); 
        });
    }
}
