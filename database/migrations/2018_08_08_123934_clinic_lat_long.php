<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ClinicLatLong extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('clinics', function($column){
            $column->float('clinic_latitude',30)->nullable()->comment('Clinic address latitude'); 
            $column->float('clinic_longitude',30)->nullable()->comment('Clinic address longitude'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('clinics')) {
            Schema::dropColumn('clinic_latitude');
            Schema::dropColumn('clinic_longitude');
        }
    }
}
