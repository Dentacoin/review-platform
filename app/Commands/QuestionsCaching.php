<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\VoxAnswersDependency;
use App\Models\VoxQuestion;
use App\Models\VoxAnswer;

use Carbon\Carbon;


class QuestionsCaching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questions:caching';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caching the dependency vox questions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo 'Caching dependency questions - START'.PHP_EOL.PHP_EOL.PHP_EOL;
        
        $dependency_questions = VoxQuestion::has('vox')->where('used_for_stats', 'dependency')->whereNotNull('stats_relation_id')->get();

        foreach ($dependency_questions as $dq) {

            $existing = VoxAnswersDependency::where('question_id', $dq->id)->first();

            if($existing) {
                $existing->delete();
            }
            
            if(!empty($dq->stats_answer_id)) {

                $results = VoxAnswer::prepareQuery($dq->id, null,[
                    'dependency_answer' => $dq->stats_answer_id,
                    'dependency_question' => $dq->stats_relation_id,
                ]);

                $results = $results->groupBy('answer')->selectRaw('answer, COUNT(*) as cnt');
                $results = $results->get();

                foreach ($results as $result) {

                    $vda = new VoxAnswersDependency;
                    $vda->question_dependency_id = $dq->stats_relation_id;
                    $vda->question_id = $dq->id;
                    $vda->answer_id = $dq->stats_answer_id;
                    $vda->answer = $result->answer;
                    $vda->cnt = $result->cnt;
                    $vda->save();
                }
            } else {
                //да минат през всички отговори
                foreach (json_decode($dq->answers, true) as $key => $single_answ) {
                    $answer_number = $key + 1;
                    
                    $results = VoxAnswer::prepareQuery($dq->id, null,[
                        'dependency_answer' => $answer_number,
                        'dependency_question' => $dq->stats_relation_id,
                    ]);

                    $results = $results->groupBy('answer')->selectRaw('answer, COUNT(*) as cnt');
                    $results = $results->get();

                    $existing = VoxAnswersDependency::where('question_id', $dq->id)->first();

                    foreach ($results as $result) {

                        $vda = new VoxAnswersDependency;
                        $vda->question_dependency_id = $dq->stats_relation_id;
                        $vda->question_id = $dq->id;
                        $vda->answer_id = $answer_number;
                        $vda->answer = $result->answer;
                        $vda->cnt = $result->cnt;
                        $vda->save();
                    }
                }
            }
        }


        echo 'Caching dependency questions - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

    }
}
