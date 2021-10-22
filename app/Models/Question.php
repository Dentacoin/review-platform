<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'question',
        'label',
        'options',
    ];

    protected $fillable = [
        'question',
        'label',
        'options',
        'order'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

class QuestionTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'question',
        'label',
        'options',
    ];
}

?>