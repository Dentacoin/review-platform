<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Secret extends Model {
    use SoftDeletes;

    
    protected $fillable = [
        'secret',
        'used',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static function getNext() {
        $found = self::where('used', false)->orderBy('id', 'ASC')->first();
        if($found) {
            return $found;
        } else {
            return self::where('used', true)->orderBy('id', 'DESC')->first();
        }
    }
}

?>