<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\VoxToCategory;
use App\Models\DcnReward;
use App\Models\Reward;

use Carbon\Carbon;

use Image;
use App;
use DB;

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
        'scale_id',
        'dont_randomize_answers',
        'hasimage_social',
    ];

    protected $dates = [
        'created_at',
        'launched_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'users_percentage' => 'array',
    ];

    public function answers_count() {
        return $this->hasMany('App\Models\PollAnswer', 'poll_id', 'id')->count();
    }
    
    public function respondentsCount() {
        return PollAnswer::where('poll_id', $this->id)->count();   
    }

    public static function handleAnswerTooltip($answer) {
        $new_answer = str_replace("[/]","</span>", str_replace('"', '&quot;', $answer));
        
        return preg_replace('/\[([^\]]*)\]/', '<span class="tooltip-text" text="${1}">', $new_answer);
    }

     public function getSocialCoverPath() {
        $folder = storage_path().'/app/public/dailypolls/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'-cover.jpg';
    }

    public function getSocialCover() {
        if(!$this->hasimage_social) {
            $this->generateSocialCover();
        }
        return url('/storage/dailypolls/'.($this->id%100).'/'.$this->id.'-cover.jpg').'?rev='.$this->updated_at->timestamp;
    }

    public function generateSocialCover() {
        $path = $this->getSocialCoverPath();

        $img = Image::canvas(1200, 628, '#fff');


        if ($this->status == 'closed') {
            $img->insert( public_path().'/img-trp/cover-dailypoll-closed.png');
        } else {
            $img->insert( public_path().'/img-trp/cover-dailypoll-open.png');
        }

        $question = $this->question;
        $question = wordwrap($question, $this->status == 'closed' ? 26 : 20); 
        $lines = explode("\n", $question);
        $linesCount = count($lines);
        $lh = 50;

        $top = (630 - ($linesCount * $lh)) / 2;

        $f_top = $top;

        foreach ($lines as $line) {
            $img->text($line, $this->status == 'closed' ? 645 : 730, $top, function($font) {
                $font->file(public_path().'/fonts/Nunito-Regular.ttf');
                $font->size(41);
                $font->color('#333333');
                $font->align('left');
                $font->valign('top');
            });
            $top += $lh;
        }
        

        if ($this->status == 'closed') {

            $text = 'WE ASKED 100 PEOPLE... ';
            $img->text($text, 645, $f_top - 80, function($font) {
                $font->file(public_path().'/fonts/Nunito-Black.ttf');
                $font->size(40);
                $font->color('#333333');
                $font->align('left');
                $font->valign('top');
            });

            $img->insert( public_path().'/img-trp/cover-results-button.png' , 'top-left', 645, $top + 50 );  
        } else {
            $text = 'DAILY POLL';
            $img->text($text, 730, $f_top - 100, function($font) {
                $font->file(public_path().'/fonts/Nunito-Black.ttf');
                $font->size(67);
                $font->color('#333333');
                $font->align('left');
                $font->valign('top');
            });

            $img->insert( public_path().'/img-trp/cover-answer-button.png' , 'top-left', 730, $top + 50 );            
        }

        $img->save($path);
        $this->hasimage_social = true;
        $this->save();
    }

    public function recalculateUsersPercentage() {

        $respondents_count = PollAnswer::where('poll_id', $this->id)->count();
        $respondents_users = PollAnswer::where('poll_id', $this->id)->get();

        $arr = [];
        foreach ($respondents_users as $ru) {
            if (!empty($ru->country_id)) {

                if (!isset($arr[$ru->country_id])) {
                    $arr[$ru->country_id] = 0;
                }
                $arr[$ru->country_id] += 1;
            }
        }

        foreach ($arr as $key => $value) {
            $arr[$key] = round((($value / $respondents_count) * 100), 2);
        }

        $this->users_percentage = $arr;
        $this->save();
    }

    public function isPollRestricted($country_id) {

        $is_restricted = false;

        if(!empty($this->users_percentage) && $this->answers_count() >= 8 ) {
            $rescricted_countries = $this->users_percentage;

            if(array_key_exists($country_id, $rescricted_countries) && $rescricted_countries[$country_id] >= 20 ) {
                $is_restricted = true;
            }
        }

        return $is_restricted;
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