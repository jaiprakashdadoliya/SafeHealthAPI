<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSymptomsTableAddSnomedctConceptId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('symptoms', function ($table) {
            $table->string('snomedct_concept_id',255)->nullable()->comment('snomedct api concept_id');
            $table->string('snomedct_id',255)->nullable()->comment('snomedct api id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('symptoms', function (Blueprint $table) {
            $table->dropColumn('snomedct_concept_id');
            $table->dropColumn('snomedct_id');
        });
    }
}
