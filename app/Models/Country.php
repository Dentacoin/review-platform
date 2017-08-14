<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dimsav\Translatable\Translatable;

use App\Models\City;

class Country extends Model
{
    use SoftDeletes, Translatable;
    
    public $translatedAttributes = [
        "name"
    ];

    protected $fillable = [
        "code",
        "slug",
        "name",
        'phone_code',
        "avg_rating",
        'ratings',
    ];

    public function cities()
    {
        return $this->hasMany('App\Models\City')->orderBy('name', 'ASC');
    }

}

class CountryTranslation extends Model {

	public $timestamps = false;
	protected $fillable = ["name"];

}