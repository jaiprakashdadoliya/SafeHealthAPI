<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientSixmwtFectors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_sixmwt_fectors')) {
            Schema::create('patient_sixmwt_fectors', function (Blueprint $table) {
                $table->increments('sixmwt_fector_id')->comment('Primary key six minut walking test fector id');
                $table->integer('sixmwt_id')->comment('Sixmwt ID - Foreign Key From patient sixmwt table');
                $table->tinyInteger('fector_type')->nullable()->comment('1 for While breathing air, 2 for Walk test while breathing supplemental oxygen : L/min');
                $table->integer('fector_id')->unsigned()->nullable()->comment('Fector id');
                $table->string('before_sixmwt',255)->nullable()->comment('Before 6MWT Details');
                $table->string('after_sixmwt',255)->nullable()->comment('After 6MWT Details');
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('sixmwt_id')->references('sixmwt_id')->on('patient_sixmwts')->onUpdate('cascade');
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
        //
    }
}
