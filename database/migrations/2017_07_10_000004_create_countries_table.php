<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	
    	Schema::create('countries', function (Blueprint $table) {
    		$table->increments('id');
            $table->string('code',2)->index();
            $table->string('slug',64)->index();
    		
            $table->string('name',50);
            
            $table->string('phone_code', 8);
            $table->float('avg_rating')->index();
            $table->integer('ratings')->index();
   			$table->timestamps();
   			$table->softDeletes();
		});

    	Schema::create('country_translations', function (Blueprint $table) {
   			$table->increments('id');
			$table->integer('country_id')->unsigned();
            $table->string('locale', 3)->index();
			
            $table->string('name',50);
             
			$table->unique(['country_id','locale']);
			$table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
    	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('country_translations');
    	Schema::drop('countries');
    }
}
