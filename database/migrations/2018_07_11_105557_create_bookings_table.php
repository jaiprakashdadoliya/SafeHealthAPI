<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('booking_id')->comment('Booking primary id');
            $table->integer('timing_id')->unsigned()->comment('Timing id');
            $table->integer('user_id')->unsigned()->comment('Booked doctor id');
            $table->integer('pat_id')->unsigned()->nullable()->comment('Booking patient id');
            $table->integer('clinic_id')->unsigned()->nullable()->comment('booking clinic');
            $table->integer('booking_reason')->unsigned()->nullable()->comment('Reason for booking the appointment');
            $table->date('booking_date')->comment('Booking date');
            $table->string('booking_time',4)->nullable()->comment('Booking time');
            $table->tinyInteger('is_profile_visible')->unsigned()->default(1)->comment('1 for not allow, 2 for allow');
            $table->tinyInteger('booking_status')->unsigned()->default(1)->after('booking_time')->comment('- 1 for not started, 2 for in progress, 3 for completed');
            $table->string('ip_address')->nullable()->comment('last login ip'); 
            $table->tinyInteger('resource_type')->unsigned()->default(1)->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS'); 
            $table->integer('created_by')->unsigned()->comment('Record created by');
            $table->integer('updated_by')->unsigned()->nullable()->comment('Record updated by');
            $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for yes, 2 for no'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
