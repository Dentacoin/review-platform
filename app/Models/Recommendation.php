<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model {
    
    protected $fillable = [
        'user_id',
        'scale',
        'description',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}

?>