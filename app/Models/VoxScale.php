<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxScale extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'answers',
    ];

    protected $fillable = [
        'title',
        'answers',
    ];

    public $timestamps = false;

}

class VoxScaleTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'answers',
    ];

}



?>