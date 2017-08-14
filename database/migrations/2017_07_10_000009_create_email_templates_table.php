<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailtemplatesTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_templates', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->unique();

            $table->string('subject', 256)->nullable();
            $table->string('title', 128)->nullable();
            $table->string('subtitle', 256)->nullable();
            $table->text('content')->nullable();         

            $table->timestamps();
            $table->softDeletes();
		});
    	
    	Schema::create('email_template_translations', function (Blueprint $table) {
   			$table->increments('id');
			$table->integer('email_template_id')->unsigned();

            $table->string('subject', 256)->nullable();
            $table->string('title', 128)->nullable();
            $table->string('subtitle', 256)->nullable();
            $table->text('content')->nullable();    
			
			$table->string('locale', 3)->index();
			 
			$table->unique(['email_template_id','locale']);
			$table->foreign('email_template_id')->references('id')->on('email_templates')->onDelete('cascade');
    	});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('email_template_translations', function (Blueprint $table) {
            $table->dropForeign('email_template_translations_email_template_id_foreign');
        });

		Schema::drop('email_template_translations');
		Schema::drop('email_templates');
	}

}
