<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
            $table->string('email', 128);
            $table->string('password', 128);
            $table->string('remember_token')->nullable();

            $table->boolean('is_dentist')->default(false)->nullable();
            $table->boolean('is_partner')->default(false)->nullable();
            
            $table->enum('title', ['dr','prof'])->nullable();
            $table->string('name', 128);
            $table->string('slug', 128)->index();
            $table->string('zip', 10)->nullable();
            $table->string('address', 128)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('website', 512)->nullable();


            
            $table->boolean('is_verified')->nullable();
            $table->timestamp('verified_on')->nullable();
            
            $table->string('verification_code', 8)->nullable();
            $table->boolean('phone_verified')->nullable();
            $table->timestamp('phone_verified_on')->nullable();
            

            $table->integer('city_id')->nullable()->index();
            $table->integer('country_id')->nullable()->index();
            $table->float('avg_rating')->nullable()->index();
            $table->integer('ratings')->nullable()->index();
            $table->integer('invited_by')->nullable();

            $table->boolean('hasimage')->nullable();

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
        Schema::dropIfExists('users');
    }
}
