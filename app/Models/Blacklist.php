<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\BlacklistBlock;

class Blacklist extends Model {
    
    protected $fillable = [
        'pattern',
        'field',
        'comments'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function blacklistBlock() {
        return $this->hasMany('App\Models\BlacklistBlock', 'blacklist_id', 'id');
    }

    public function delete() {
        $this->blacklistBlock()->delete();
        parent::delete();
    }
    
}

?>