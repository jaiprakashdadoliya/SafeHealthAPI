<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DoctorLatLong extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('doctors', function($column){
            $column->string('doc_latitude',30)->nullable()->comment('Doctor address latitude'); 
            $column->string('doc_longitude',30)->nullable()->comment('Doctor address longitude'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('doctors')) {
            Schema::dropColumn('doc_latitude');
            Schema::dropColumn('doc_longitude');
        }
    }
}
