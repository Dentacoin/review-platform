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
            $dq->generateDependencyCaching();
        }

        echo 'Caching dependency questions - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

    }
}
