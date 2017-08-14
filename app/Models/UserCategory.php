<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCategory extends Model {

	public $timestamps = false;

    protected $fillable = [
        'category_id',
        'user_id',
    ];
}


?>