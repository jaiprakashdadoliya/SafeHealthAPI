<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorReferralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('doctor_referral')) {
            Schema::create('doctor_referral', function (Blueprint $table) {
                $table->increments('doc_ref_id')->comment('Doctor referral Id');
                $table->string('doc_ref_name','150')->nullable()->comment('Doctor referral Name');
                $table->integer('user_id')->unsigned()->comment('Foreign Key from users table');
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctor_referral');
    }
}
