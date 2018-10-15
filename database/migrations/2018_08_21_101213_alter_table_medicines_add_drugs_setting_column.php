<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMedicinesAddDrugsSettingColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medicines', function ($table) {
            $table->integer('drug_type_id')->nullable()->comment('drug_type_id is primary id of drug_type table');
            $table->integer('drug_dose_unit_id')->nullable()->comment('drug_dose_unit_id is primary id of drug_dose_unit table');
            $table->string('medicine_dose',255)->nullable()->comment('medicine dose given');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('drug_type_id'); 
            $table->dropColumn('drug_dose_unit_id'); 
            $table->dropColumn('medicine_dose'); 
        });
    }
}
