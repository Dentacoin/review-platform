<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxAnswer extends Model {
    
    protected $fillable = [
        'user_id',
        'vox_id',
        'question_id',
        'answer',
        'scale',
        'country_id',
        'is_scam',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
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
    
}





?>