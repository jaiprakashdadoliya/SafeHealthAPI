<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInvestigationReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('investigation_report')) {
            Schema::create('investigation_report', function (Blueprint $table) {
                $table->increments('ir_id')->unsigned()->comment('Investigation report primary ID');
                $table->integer('visit_id')->unsigned()->index()->comment('Visit ID');
                $table->integer('pat_id')->unsigned()->index()->comment('Patient ID');
                $table->integer('report_type')->unsigned()->nullable()->comment('Primary ID from laboratory report table');
                $table->string('report_file', 255)->nullable()->comment('Uploaded report file');
                $table->text('report_description')->nullable()->comment('Report description');
                $table->string('ip_address',50)->nullable()->comment('User last login ip'); 
                $table->tinyInteger('resource_type')->unsigned()->nullable()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('Record created by. 0 for self');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('Record updated by. 0 for self');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for yes, 2 for no');
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
