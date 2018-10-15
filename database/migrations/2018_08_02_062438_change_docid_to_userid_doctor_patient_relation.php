<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDocidToUseridDoctorPatientRelation extends Migration
{
   public function up()
    {
        Schema::table('doctor_patient_relation', function(Blueprint $table) {
            $table->renameColumn('doc_id', 'user_id');
        });
    }


    public function down()
    {
        Schema::table('doctor_patient_relation', function(Blueprint $table) {
            $table->renameColumn('user_id', 'doc_id');
        });
    }
}
