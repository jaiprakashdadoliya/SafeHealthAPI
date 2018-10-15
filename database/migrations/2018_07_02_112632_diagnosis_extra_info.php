<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DiagnosisExtraInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('diagnosis_extra_info')) {
            Schema::create('diagnosis_extra_info', function (Blueprint $table) {
                $table->increments('dei_id')->comment('Patients diagnosis extra info id');
                $table->integer('visit_diagnosis_id')->comment('Visit diagnosis ID - Foreign Key From patients visit diagnosis table');
                $table->integer('diagnosis_fector_key')->comment('Diagnosis fector id');
                $table->string('diagnosis_fector_value', 255)->comment('Diagnosis fector value');
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
        //
    }
}
