<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdhaarCard extends Migration
{
     public function up()
    {
         Schema::table('users', function($column){
            $column->string('user_adhaar_number')->nullable()->after('user_password')->comment('User adhaar card or udid'); 
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($column){
            $column->dropColumn('user_adhaar_number'); 
        });
    }
}
