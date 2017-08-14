<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ReviewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $j=0;
        for($i=5;$i<=100;$i++) {

            for($back=1;$back<=4;$back++) {
                $j++;

                $uv = rand(0,5);

                DB::table('reviews')->insert([
                    'user_id' => $i,
                    'dentist_id' => $i-$back,
                    'rating' => rand(2,5),
                    'verified' => rand(0,1),
                    'upvotes' => $uv,
                    'answer' => [
                        'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                        'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                        'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.',
                        'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum',
                    ][rand(0,3)],
                    'reply' => rand(0,1) ? [
                        'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                        'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                        'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.',
                        'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum',
                    ][rand(0,3)] : null
                ]);

                for($p=0;$p<$uv;$p++) {
                    DB::table('review_upvotes')->insert([
                        'review_id' => $j,
                        'user_id' => $i-($p+1)
                    ]);

                }

                for($p=1;$p<=10;$p++) {
                    DB::table('review_answers')->insert([
                        'review_id' => $j,
                        'question_id' => $p,
                        'rating' => rand(1,5),
                        'options' => json_encode([
                            rand(1,5),
                            rand(1,5),
                            rand(1,5),
                            rand(1,5),
                        ])
                    ]);
                }
            }
            
        }

    }
}
