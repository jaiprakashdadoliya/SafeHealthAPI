<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DoctorsSpecialisations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('doctors_specialisations')) {
            Schema::create('doctors_specialisations', function (Blueprint $table) {
                $table->increments('doc_spl_id')->comment('Primary Key of Doctor Specialisations table');
                $table->integer('user_id')->unsigned()->comment('Foreign Key from user table');
                $table->integer('user_type')->nullable()->default(2)->comment('User Types : 2 for doctors, 3 for patients'); 
                $table->integer('spl_id')->unsigned()->comment('Foreign Key from specialisation table');
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
        Schema::dropIfExists('doctors_specialisations');
    }
}
