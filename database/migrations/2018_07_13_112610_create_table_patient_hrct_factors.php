<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientHrctFactors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_hrct_factors')) {
            Schema::create('patient_hrct_factors', function (Blueprint $table) {
                $table->increments('phrctf_id')->comment('Primary key patient_hrct_factors table');
                $table->integer('phrct_id')->index()->comment('Foreign Key From patient_hrct');

                $table->integer('phrct_factor_id')->nullable()->comment('factor id');
                $table->tinyInteger('phrct_factor_value')->nullable()->comment('factor value');
                
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('phrct_id')->references('phrct_id')->on('patient_hrct')->onUpdate('cascade');
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
        Schema::dropIfExists('patient_hrct_factors');
    }
}
