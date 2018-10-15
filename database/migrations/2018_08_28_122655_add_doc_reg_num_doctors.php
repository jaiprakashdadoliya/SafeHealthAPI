<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocRegNumDoctors extends Migration
{
    public function up()
    {
         Schema::table('doctors', function($column){
            $column->string('doc_reg_num',10)->after('user_id')->nullable()->comment('Doctor registration number'); 
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctors', function($column){
            $column->dropColumn('doc_reg_num'); 
        });
    }
}
