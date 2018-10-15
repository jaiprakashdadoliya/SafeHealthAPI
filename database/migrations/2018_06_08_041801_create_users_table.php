<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('user_id')->unsigned()->comment('User Primary ID');
                $table->tinyInteger('user_type')->unsigned()->index()->comment('User Types : 1 for super admin, 2 for admin, 3 for investigator, 4 for Patient');
                $table->tinyInteger('user_gender')->unsigned()->comment('User gender : 1 for Male, 2 for Female, 3 for Transgender');
                $table->string('user_firstname', 150)->index()->nullable()->comment('User first name');
                $table->string('user_lastname', 150)->index()->nullable()->comment('User last name');
                $table->string('user_country_code',10)->comment('User mobile country code');
                $table->string('user_mobile',15)->unique()->comment('User mobile number');
                $table->string('user_email', 150)->unique()->comment('User email address');            
                $table->string('user_password',255)->nullable()->comment('User password');
                $table->tinyInteger('user_status')->unsigned()->index()->default(1)->comment('User Status - 1 for pending, 2  For approved, 3 for unapproved');
                $table->tinyInteger('user_is_mob_verified')->unsigned()->default(2)->comment('1 for yes, 2 for no');
                $table->tinyInteger('user_is_email_verified')->unsigned()->default(2)->comment('1 for yes, 2 for no');
                $table->string('ip_address',50)->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no'); 
                $table->timestamps();
                $table->rememberToken();  
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
        if (Schema::hasTable('users')) {
            Schema::dropIfExists('users');
        }
    }
}