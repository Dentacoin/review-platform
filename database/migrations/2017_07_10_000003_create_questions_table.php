<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function(Blueprint $table)
        {
            $table->increments('id');
                        
            $table->string('label', 512)->nullable();
            $table->string('question', 512)->nullable();
            $table->text('options')->nullable();
            $table->integer('order')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('question_translations', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('question_id')->unsigned();
            $table->string('locale', 3)->index();
            
            $table->string('label', 512)->nullable();
            $table->string('question', 512)->nullable();
            $table->text('options')->nullable();

            $table->unique(['question_id','locale']);
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('question_translations', function (Blueprint $table) {
            $table->dropForeign('question_translations_question_id_foreign');
        });

        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_translations');
    }
}
