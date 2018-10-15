<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorsAwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('doctors_awards')) {
            Schema::create('doctors_awards', function (Blueprint $table) {
                $table->increments('doc_award_id')->comment('Doctors Award ID');
                $table->Integer('user_id')->index()->comment('Foreign Key From user table');
                $table->tinyInteger('user_type')->nullable()->default('1')->comment('User Types : 2 for doctors, 3 for patients');
                $table->string('doc_award_name')->comment('Award Name');
                $table->tinyInteger('doc_award_year')->nullable()->comment('Award achievement year');
                $table->tinyInteger('doc_award_status')->nullable()->default('1')->comment('Award Status of row - 1 For active, 2 for deleted');
                $table->tinyInteger('resource_type')->default('1')->comment('Resource type - 1 For Web, 2 for Android and 3 for IOS');
                $table->string('ip_address', 20)->comment('ip address of the device');
                $table->integer('created_by')->comment('User who created award');
                $table->integer('updated_by')->comment('User who updated award');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for yes, 2 for no');
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
        Schema::dropIfExists('doctors_awards');
    }
}
