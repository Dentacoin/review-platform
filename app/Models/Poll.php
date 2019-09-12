<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Image;

use DB;
use App;
use Carbon\Carbon;
use App\Models\VoxToCategory;
use App\Models\Reward;
use App\Models\DcnReward;

class Poll extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'question',
        'answers',
    ];

    protected $fillable = [
        'question',
        'answers',
        'category',
        'status',
    ];

    protected $dates = [
        'created_at',
        'launched_at',
        'updated_at',
        'deleted_at'
    ];
    
    // public function respondentsCount() {
    //     return DcnReward::where('reference_id', $this->id)->where('platform', 'vox')->where('type', 'daily_poll')->has('user')->count();   
    // }
    
    public function respondentsCount() {
        return PollAnswer::where('poll_id', $this->id)->count();   
    }

    public static function handleAnswerTooltip($answer) {
        $new_answer = str_replace("[/]","</span>", str_replace('"', '&quot;', $answer));
        
        return preg_replace('/\[([^\]]*)\]/', '<span class="tooltip-text" text="${1}">', $new_answer);
    }
    
}

class PollTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'question',
        'answers',
    ];

}



?>