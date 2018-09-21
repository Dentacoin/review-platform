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
        'is_completed',
        'is_skipped',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function getCount() {
        $fn = storage_path('vox_count');
        $t = file_exists($fn) ? filemtime($fn) : null;
        if(!$t || $t < time()-300) {
            $cnt = self::count();
            file_put_contents($fn, $cnt);
        }
        return file_get_contents($fn);
    }
    
}





?>