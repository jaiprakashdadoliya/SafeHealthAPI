<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class AddColumnDocSlugToDoctorsTable extends Migration
{
     public function up()
    {
         Schema::table('doctors', function($column){
            $column->string('doc_slug')->after('user_id')->nullable()->comment('Doctor unique slug'); 
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
            $column->dropColumn('doc_slug'); 
        });
    }
}