<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PollsMonthlyDescription extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'description',
    ];

    protected $fillable = [
        'description',
        'month',
        'year',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

class PollsMonthlyDescriptionTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'description',
    ];

}



?>