<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocConsultFee extends Migration
{
     public function up()
    {
         Schema::table('doctors', function($column){
            $column->integer('doc_consult_fee')->after('user_id')->unsigned()->nullable()->comment('Doctor consult fees'); 
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
            $column->dropColumn('doc_consult_fee'); 
        });
    }
}
