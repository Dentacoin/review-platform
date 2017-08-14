<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
	$isd = rand(0,1);

    $ret = [
        'name' =>$faker->firstName.' '.$faker->lastName,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'is_dentist' => $isd,
        'is_partner' => $isd ? rand(0,1) : 0,
        'city_id' => [14408,24640,91290,68559][rand(0,3)],
        'country_id' => 34,
        'phone' => rand(100000,999999),
    ];

    if($isd) {
    	$ret['avg_rating'] = rand(30,50)/10;
    	$ret['ratings'] = rand(1,15);
    	$ret['title'] = ['dr','prof'][rand(0,1)];
    	$ret['zip'] = rand(1000,9999);
    	$ret['address'] = $faker->streetAddress();
    	$ret['website'] = $faker->url();
    }

    return $ret;
});