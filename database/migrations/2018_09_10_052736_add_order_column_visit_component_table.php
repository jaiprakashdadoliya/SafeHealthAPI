<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderColumnVisitComponentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visits_components', function($column){
            $column->integer('visit_cmp_order')->unsigned()->nullable()->comment('Component ordering'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visits_components', function (Blueprint $table) {
            $table->dropColumn('visit_cmp_order'); 
        });
    }
}
