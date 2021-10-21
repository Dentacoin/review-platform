<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dimsav\Translatable\Translatable;

class City extends Model {
    
    use SoftDeletes, Translatable;
    
    public $translatedAttributes = [
        "name"
    ];

    protected $fillable = [
        "code",
        "slug",
        "name",
        'country_id',
        'avg_rating',
        'ratings',
    ];
    
    public function country() {
        return $this->hasOne('App\Models\Country', 'id', 'country_id');
    }

}

class CityTranslation extends Model {

	public $timestamps = false;
	protected $fillable = ["name"];

}