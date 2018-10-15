<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_verifications')) {
            Schema::create('user_verifications', function (Blueprint $table) {
                $table->increments('user_ver_id')->unsigned()->comment('User verification primary id');
                $table->integer('user_id')->unsigned()->index()->comment('User id foreign key, refer tbl_user.user_id');
                $table->string('user_ver_object', 150)->index()->comment('this field will store mobile number or email for verification');
                $table->tinyInteger('user_ver_obj_type')->index()->unsigned()->comment('1 for mobile, 2 for email');
                $table->string('user_ver_hash_otp',255)->comment('this field will store hash in case of email verification and otp in case of mobile verification');
                $table->timestamp('user_ver_expiredat')->comment('This field contains otp expiry time');
                $table->string('ip_address',50)->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by');
                $table->tinyInteger('is_deleted')->unsigned()->default(2)->comment('1 for yes, 2 for no'); 
                $table->timestamps();
                $table->foreign('user_id')->references('user_id')->on('users')->onUpdate('cascade');
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
        Schema::dropIfExists('user_verifications');
    }
}
