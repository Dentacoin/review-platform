<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	
    	Schema::create('cities', function (Blueprint $table) {
    		$table->increments('id');
            $table->string('slug',64)->index();
            $table->integer('country_id')->unsigned();
    		
            $table->string('name',50);
            
            $table->float('avg_rating')->index();
            $table->integer('ratings')->index();
   			$table->timestamps();
   			$table->softDeletes();
		});

    	Schema::create('city_translations', function (Blueprint $table) {
   			$table->increments('id');
			$table->integer('city_id')->unsigned();
            $table->string('locale', 3)->index();
			
            $table->string('name',50);
             
			$table->unique(['city_id','locale']);
			$table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
    	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('city_translations');
    	Schema::drop('cities');
    }
}
