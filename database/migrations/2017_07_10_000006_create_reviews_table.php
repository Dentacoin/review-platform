<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('dentist_id')->index();
            $table->float('rating')->nullable();
            $table->integer('upvotes')->nullable();
            $table->boolean('verified')->nullable();
            $table->text('answer')->nullable();
            $table->text('reply')->nullable();
            $table->integer('secret_id')->nullable();
            $table->enum('status', ['pending', 'accepted'])->nullable();
            
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
