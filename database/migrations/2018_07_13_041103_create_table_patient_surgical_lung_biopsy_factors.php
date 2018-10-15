<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientSurgicalLungBiopsyFactors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_surgical_lung_biopsy_factors')) {
            Schema::create('patient_surgical_lung_biopsy_factors', function (Blueprint $table) {
                $table->increments('pslbf_id')->comment('Primary key patient_surgical_lung_biopsy_factors table');
                $table->integer('pslb_id')->index()->comment('Foreign Key From patinet_surgical_lung_biopsy');

                $table->integer('pslbf_factor_id')->comment('factor id');
                $table->string('pslbf_factor_value',255)->comment('factor value');
                
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
        Schema::dropIfExists('patient_surgical_lung_biopsy_factors');
    }
}
