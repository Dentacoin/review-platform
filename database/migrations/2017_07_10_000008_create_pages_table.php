<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function(Blueprint $table)
        {
            $table->increments('id');
            $table->boolean('hasimage')->nullable()->index();
            $table->integer('header')->nullable();
            $table->integer('footer')->nullable();
            
            $table->string('slug', 128)->nullable()->unique();
            $table->string('seo_title', 256)->nullable();
            $table->string('description', 512)->nullable();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });

        Schema::create('page_translations', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('page_id')->unsigned();
            $table->string('locale', 3)->index();
            
            $table->string('slug', 128)->nullable();
            $table->string('seo_title', 256)->nullable();
            $table->string('description', 512)->nullable();
            $table->string('title')->nullable();
            $table->text('content')->nullable();

            $table->unique(['page_id','locale']);
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
}
