<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionScammersByDay extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'checked',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
}

?>