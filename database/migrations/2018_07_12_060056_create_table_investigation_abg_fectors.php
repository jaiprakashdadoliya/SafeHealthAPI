<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInvestigationAbgFectors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if (!Schema::hasTable('investigation_abg_fector')) {
            Schema::create('investigation_abg_fector', function (Blueprint $table) {
                $table->increments('iaf_id')->comment('investigation_abg unique id');
                $table->integer('ia_id')->index()->comment('ia_id - foreign key from investigation_abg table');
                $table->integer('fector_id')->unsigned()->nullable()->comment('investigation abg fector id');
                $table->string('fector_value',255)->nullable()->comment('investigation abg fector value');
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('ia_id')->references('ia_id')->on('investigation_abg')->onUpdate('cascade');
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
        Schema::dropIfExists('investigation_abg_fector');
    }
}
