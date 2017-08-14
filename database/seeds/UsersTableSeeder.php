<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\User::class, 100)->create();


        for($i=1;$i<100;$i++) {

        	$c1 = rand(0,2);
        	$c2 = rand(3,4);
            DB::table('user_categories')->insert([
                'user_id' => $i,
                'category_id' => $c1,
            ]);
            DB::table('user_categories')->insert([
                'user_id' => $i,
                'category_id' => $c2,
            ]);
        }

    }
}
