<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDbwriteOpertaionLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('dbwrite_opertaion_log')) {
            Schema::create('dbwrite_opertaion_log', function (Blueprint $table) {
                $table->bigIncrements('wol_id')->comment('Primary key dbwrite_opertaion_log table');
                $table->string('wol_table',100)->nullable()->comment('Operation perform table name');
                $table->tinyInteger('wol_type')->index()->nullable()->comment('Operation perform: 1 for insert, 2 for update');
                $table->json('wol_data')->nullable()->comment('Stores all data in json format');
                $table->json('wol_where')->nullable()->comment('Stores all where condition in json format');
                $table->text('wol_custom_where')->nullable()->comment('Stores custom query where condition');
                $table->integer('wol_user_id')->unsigned()->nullable()->default(0)->comment('Refer userid from users table');
                $table->string('wol_ip',50)->nullable()->comment('Client IP address');
                $table->tinyInteger('wol_resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->timestamp('wol_created_at')->comment('Postgres timestamp');
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
        Schema::dropIfExists('dbwrite_opertaion_log');
    }
}
