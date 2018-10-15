<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientThoracoscopicLungBiopsy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_thoracoscopic_lung_biopsy')) {
            Schema::create('patient_thoracoscopic_lung_biopsy', function (Blueprint $table) {
                $table->increments('ptlb_id')->unsigned()->comment('Patients patient_thoracoscopic_lung_biopsy unique id');
                $table->integer('pat_id')->unsigned()->index()->comment('Foreign key from users table');
                $table->integer('visit_id')->unsigned()->index()->comment('Foreign key from patient_visit table');
                $table->date('ptlb_date')->nullable()->comment('Date of  thoracoscopic lung biopsy');
                $table->tinyInteger('ptlb_is_happen')->unsigned()->nullable()->comment('R1 for yes, 2 for no'); 
                $table->tinyInteger('ptlb_is_left_lung')->unsigned()->nullable()->default(2)->comment('R1 for yes, 2 for no'); 
                $table->tinyInteger('ptlb_is_right_lung')->unsigned()->nullable()->default(2)->comment('R1 for yes, 2 for no'); 
                $table->tinyInteger('ptlb_left_lung_lobe')->unsigned()->nullable()->default(0)->comment('1 for upper lobe, 2 middle lobe, 3 lower'); 
                $table->tinyInteger('ptlb_right_lung_lobe')->unsigned()->nullable()->default(0)->comment('1 for upper lobe, 2 middle lobe, 3 lower'); 
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->index()->default(2)->comment('1 for yes, 2 for no');
                $table->foreign('pat_id')->references('user_id')->on('users')->onUpdate('cascade');
                $table->foreign('visit_id')->references('visit_id')->on('patients_visits')->onUpdate('cascade');
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
        Schema::dropIfExists('patient_thoracoscopic_lung_biopsy');
    }
}
