<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowIsColumnInVisitsComponentsTable extends Migration
{
    /* Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visits_components_settings', function($column){
            $column->integer('show_in')->unsigned()->nullable()->default(2)->comment('show in followup visit or not, 1 for no, 2 for yes'); 
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
            $table->dropColumn('show_in'); 
        });
    }
}

