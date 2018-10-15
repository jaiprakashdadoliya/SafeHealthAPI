<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RegistrationLoginChangesInStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctors_staff', function (Blueprint $table) {
            $table->integer('doc_user_id')->nullable()->after('doc_staff_id')->unsigned()->default(0)->comment('Foregn key for user_id of the doctor from user table');
            $table->string('doc_staff_name')->nullable()->comment('Name of Staff Person')->change();
            $table->string('doc_staff_mobile', 15)->nullable()->comment('Mobile Number of Staff Person')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctors_staff', function (Blueprint $table) {
            $table->dropColumn('doc_user_id'); 
            $table->string('doc_staff_name')->comment('Name of Staff Person')->change();
            $table->string('doc_staff_mobile', 15)->comment('Mobile Number of Staff Person')->change();
        });
    }
}
