<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('doctors')) {
            Schema::create('doctors', function (Blueprint $table) {
                $table->increments('doc_id')->unsigned()->comment('Doctor primary id');
                $table->integer('user_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->mediumText('doc_short_info')->nullable()->comment('doctor short info');
                $table->string('doc_profile_img', 100)->nullable()->comment('Doctor profile image');
                $table->string('doc_address_line1', 255)->nullable()->comment('Doctor address line1');
                $table->string('doc_address_line2', 255)->nullable()->comment('Doctor address line2');
                $table->integer('city_id')->unsigned()->index()->nullable()->comment('Doctor city');
                $table->string('doc_registration_number', 100)->nullable();
                $table->string('doc_state_council', 155)->nullable();
                $table->string('doc_other_city', 100)->nullable()->comment('If user select other city');
                $table->tinyInteger('state_id')->unsigned()->nullable()->index()->comment('doctor state');
                $table->string('doc_locality', 100)->nullable();
                $table->string('doc_pincode', 10)->nullable()->comment('Doctor address pin code');
                $table->integer('center_code')->nullable()->unique()->comment('Doctor center code.');
                $table->string('doc_facebook_url', 150)->nullable()->comment('doctor facebook profile url');
                $table->string('doc_twitter_url', 150)->nullable()->comment('doctor twitter profile url');
                $table->string('doc_google_url', 150)->nullable()->comment('doctor google profile url');
                $table->string('doc_linkedin_url', 150)->nullable()->comment('doctor linkedin profile url');
                $table->string('ip_address',50)->comment('last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for yes, 2 for no'); 
                $table->timestamps();
                $table->foreign('user_id')->references('user_id')->on('users')->onUpdate('cascade');
                $table->foreign('state_id')->references('id')->on('states')->onUpdate('cascade');
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
        Schema::dropIfExists('doctors');
    }
}
