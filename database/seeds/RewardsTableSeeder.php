<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RewardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('rewards')->insert([
            'reward_type' => 'regular',
            'amount' => 0
        ]);
        DB::table('rewards')->insert([
            'reward_type' => 'invited',
            'amount' => 0
        ]);
        DB::table('rewards')->insert([
            'reward_type' => 'useful',
            'amount' => 0
        ]);

    }
}
