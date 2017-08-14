<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_answers', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('review_id')->index();
            $table->integer('question_id')->index();
            $table->integer('rating')->nullable();
            $table->text('options')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['review_id','question_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_answers');
    }
}
