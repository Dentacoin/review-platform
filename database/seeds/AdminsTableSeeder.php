<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            'username' => 'dok',
            'password' => bcrypt('dokdok'),
            'email' => 'official@youpluswe.com',
        ]);

        DB::table('admins')->insert([
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'email' => 'dcn@dentaprime.com',
        ]);

    }
}
