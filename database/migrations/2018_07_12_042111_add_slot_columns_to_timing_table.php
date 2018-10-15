<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlotColumnsToTimingTable extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('timing', function($column){
                $column->integer('slot_duration')->unsigned()->nullable()->default(30)->comment('Duration of time within the slot');
                $column->tinyInteger('patients_per_slot')->unsigned()->nullable()->default(4)->comment('Number of slots to be booked within the time slot in minutes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('timing', function($column){
            $column->dropColumn('slot_duration'); 
            $column->dropColumn('patients_per_slot'); 
        });
    }
}
