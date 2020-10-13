<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailValidation extends Model {
    use SoftDeletes;

    protected $fillable = [
    	"email",
        "meta",
        "valid",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at', 
        'deleted_at'
    ];
        
}