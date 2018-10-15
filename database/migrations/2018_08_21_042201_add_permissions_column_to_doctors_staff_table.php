<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionsColumnToDoctorsStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctors_staff', function (Blueprint $table) {
            $table->string('doc_staff_permissions')->nullable()->after('doc_staff_role')->comment('Permissions list for content visible to the staff in JSON');
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
            $table->dropColumn('doc_staff_permissions'); 
        });
    }
}


