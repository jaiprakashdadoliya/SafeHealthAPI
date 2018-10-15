<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsGeneralCheckupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('patients_general_checkup')) {
            Schema::create('patients_general_checkup', function (Blueprint $table) {
                $table->increments('pat_checkup_id')->comment('Patient General Checkup ID');
                $table->integer('pat_id')->comment('User ID - Foreign Key From users table');
                $table->integer('visit_id')->comment('Visit ID - Foreign Key From patient_visit table');
                $table->tinyInteger('checkup_factor_id')->comment('1 = Weight loss, 2 = Difficulty in swallowing, 3 = Dry eyes or dry mouth, 4 = Rash or changes in skin, 5 = Oedema on legs, 6 = Blood in urine, 7 = Bruising skin, 8 = Hand ulcers, 9 = Mouth ulcers, 10 = Chest Pain, 11 = Joint Pain, 12 Symptoms of gastro-oesophagial reflux (GERD), 13 = Indigestion, 14 = Heartburn, 15 = Acid sour taste, 16 = Belching, 17 = Bloating sensation, 18 = Cough after meals, 19 Cough at night times/sleeping');
                $table->tinyInteger('is_happend')->unsigned()->nullable()->default(0)->comment('Happend or Not - 1 For yes , 2 for not');
                $table->integer('duration')->unsigned()->nullable()->comment('Duration');
                $table->tinyInteger('duration_unit')->unsigned()->nullable()->comment('1 for day, 2 for week, 3 for month');
                $table->text('remark')->nullable()->comment('Remark from doctor');                      
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
                $table->timestamps();
                $table->foreign('pat_id')->references('user_id')->on('users')->onUpdate('cascade');
                $table->foreign('visit_id')->references('visit_id')->on('patients_visits')->onUpdate('cascade');
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
        Schema::table('patients_general_checkup', function (Blueprint $table) {
            //
        });
    }
}
