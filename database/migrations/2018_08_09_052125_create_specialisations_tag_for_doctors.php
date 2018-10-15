<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecialisationsTagForDoctors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('doctor_specialisations_tags')) {
            Schema::create('doctor_specialisations_tags', function (Blueprint $table) {
                $table->increments('doc_spl_tag_id')->comment('Primary Key of doctor specialisation tag table');
                $table->string('specailisation_tag','100')->nullable()->comment('specialisation tag name');
                $table->integer('user_id')->unsigned()->comment('Foreign Key from users table');
                $table->integer('doc_spl_id')->unsigned()->comment('Foreign Key from doctor specialisation table');
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
         Schema::dropIfExists('doctor_specialisations_tags');
    }
}
