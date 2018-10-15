<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewRatingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       if (!Schema::hasTable('review_rating')) {
            Schema::create('review_rating', function (Blueprint $table) {
                $table->increments('rev_rat_id')->comment('Review rating Id');
                $table->integer('overall')->unsigned()->nullable()->comment('Overall Rating');
                $table->integer('wait_time')->unsigned()->nullable()->comment('Waiting time rating');
                $table->integer('manner')->unsigned()->nullable()->comment('Badside manner rating');
                $table->integer('review_user_id')->unsigned()->nullable()->comment('Reviewer id');
                $table->integer('user_id')->unsigned()->nullable()->comment('Foreign Key from users table');
                $table->text('comment')->nullable()->comment('Patient comment');
                $table->smallInteger('is_visible')->nullable()->default(1)->comment('Comment visible - 1 For Yes , 2 for No');
                $table->tinyInteger('resource_type')->unsigned()->nullable()->comment('Resource type - 1 For Web  , 2 for Android and 3 for IOS');
                $table->string('ip_address',50)->nullable()->comment('User last login ip');
                $table->integer('created_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->integer('updated_by')->unsigned()->nullable()->default(0)->comment('0 for self/by system');
                $table->tinyInteger('is_deleted')->unsigned()->nullable()->default(2)->comment('1 for deleted yes and 2 for deleted no');
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
        Schema::dropIfExists('review_rating');
    }
}
