<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PagesTableSeeder::class);
        //$this->call(ReviewsTableSeeder::class);
        $this->call(QuestionsTableSeeder::class);
        $this->call(EmailTemplatesTableSeeder::class);
        $this->call(RewardsTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
        //$this->call(UsersTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
    }
}
