<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class VoxAnswer extends Model {
    
    protected $fillable = [
        'user_id',
        'vox_id',
        'question_id',
        'answer',
        'scale',
        'country_id',
        'gender',
        'marital_status',
        'children',
        'education',
        'employment',
        'age',
        'household_children',
        'job_title',
        'income',
        'is_scam',
        'is_completed',
        'is_skipped',
        'is_admin'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function question() {
        return $this->hasOne('App\Models\VoxQuestion', 'id', 'question_id');
    }
    
    public function country() {
        return $this->hasOne('App\Models\Country', 'id', 'country_id');
    }

    public static function getCount($reload=false) {
        $fn = storage_path('vox_count');
        $t = file_exists($fn) ? filemtime($fn) : null;
        if($reload || !$t || $t < time()-3600) {
            $cnt = self::count();
            file_put_contents($fn, $cnt);
        }
        return file_get_contents($fn);
    }

    public static function prepareQuery($question_id, $dates, $options = []) {

        $results = self::whereNull('is_admin')
        ->where('question_id', $question_id)
        ->where('is_completed', 1)
        ->where('is_skipped', 0)
        ->has('user');        

        if( isset($options['dependency_question']) && isset($options['dependency_answer']) ) {

            $q = $options['dependency_question'];
            $a = $options['dependency_answer'];
            $results = $results->whereIn('user_id', function($query) use ($q, $a) {
                $query->select('user_id')
                ->from('vox_answers')
                ->where('question_id', $q)
                ->where('answer', $a);
            } );

        }

        if( isset($options['scale_answer_id']) ) {
            $results = $results->where('answer', $options['scale_answer_id']);
        }

        if( isset($options['scale_options']) && isset( $options['scale'] ) ) {
            //dd($scale_options, $scale);
            $results = $results->whereIn($options['scale'], array_values($options['scale_options']));
        }

        if(is_array($dates)) {
            $from = Carbon::parse($dates[0]);
            $to = Carbon::parse($dates[1]);
            $results = $results->where('created_at', '>=', $from)->where('created_at', '<=', $to);
        } else if($dates) {
            $from = Carbon::parse($dates);
            $results = $results->where('created_at', '>=', $from);
        }

        return $results;
    }
    
}





?>