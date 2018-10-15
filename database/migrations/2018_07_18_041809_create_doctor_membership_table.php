<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorMembershipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        if (!Schema::hasTable('doctor_membership')) {
            Schema::create('doctor_membership', function (Blueprint $table) {
                $table->increments('doc_mem_id')->comment('Doctors Membership ID');
                $table->integer('user_id')->index()->comment('Foreign Key From user table');
                $table->integer('user_type')->nullable()->default(2)->comment('User Types : 2 for doctors, 3 for patients'); 
                $table->string('doc_mem_name', 150)->comment('Membership Name');
                $table->string('doc_mem_no', 150)->nullable()->comment('Membership Number');
                $table->smallInteger('doc_mem_year')->nullable()->comment('Membership Joining Year');
                $table->tinyInteger('doc_mem_status')->nullable()->default(1)->comment('Doctor Status - 1 For active  , 2 for deleted');
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
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
        Schema::dropIfExists('doctor_membership');
    }
}
