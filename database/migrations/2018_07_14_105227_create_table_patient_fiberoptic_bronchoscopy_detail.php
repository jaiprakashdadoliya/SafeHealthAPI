<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePatientFiberopticBronchoscopyDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patient_fiberoptic_bronchoscopy_detail')) {
            Schema::create('patient_fiberoptic_bronchoscopy_detail', function (Blueprint $table) {
                $table->increments('pfbd_id')->comment('Primary key patient_fiberoptic_bronchoscopy_detail table');
                $table->integer('pfb_id')->index()->comment('Foreign Key From patient_hrct');

                $table->tinyInteger('pfbd_test_id')->nullable()->comment('test id from static data');
                $table->tinyInteger('pfbd_type')->nullable()->comment('1 for result, 2 for report');
                $table->string('pfbd_value',255)->nullable()->comment('Contains static array id if type 1, contains text data if type 2');
                $table->string('pfbd_per_suggestive',255)->nullable()->comment('suggestive value');
                
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('pfb_id')->references('pfb_id')->on('patient_fiberoptic_bronchoscopy')->onUpdate('cascade');
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
         Schema::dropIfExists('patient_fiberoptic_bronchoscopy_detail');
    }
}
