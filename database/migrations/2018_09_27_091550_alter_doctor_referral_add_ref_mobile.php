<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDoctorReferralAddRefMobile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_referral', function($column){
            $column->string('doc_ref_mobile', 20)->nullable()->comment('Referral Doctor contact number'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctor_referral', function($column){
            $column->dropColumn('doc_ref_mobile'); 
        });
    }
}
