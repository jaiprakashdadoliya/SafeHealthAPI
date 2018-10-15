<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocRefIdPatients extends Migration
{
     public function up()
    {
         Schema::table('patients', function($column){
            $column->integer('doc_ref_id')->unsigned()->nullable()->comment('Referral doctor id'); 
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function($column){
            $column->dropColumn('doc_ref_id'); 
        });
    }
}
