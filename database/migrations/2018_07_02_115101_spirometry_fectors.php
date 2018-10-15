<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SpirometryFectors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('spirometry_fectors')) {
            Schema::create('spirometry_fectors', function (Blueprint $table) {
                $table->increments('sf_id')->comment('Primary key investigation fector id');
                $table->integer('spirometry_id')->comment('Spirometry ID - Foreign Key From spirometries table');
                $table->integer('fector_id')->comment('Fector Id');
                $table->string('fector_value', 255)->nullable()->comment('Fector value');                
                $table->string('fector_pre_value', 255)->nullable()->comment('Fector Pre Value');
                $table->string('fector_post_value', 255)->nullable()->comment('Fector Post Value');
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('spirometry_id')->references('spirometry_id')->on('spirometries')->onUpdate('cascade');
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
