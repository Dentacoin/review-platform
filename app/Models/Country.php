<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class Country extends Model {

    use SoftDeletes, Translatable;

    protected static function boot() {
        parent::boot();
     
        // Order by name ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('name', 'asc');
        });
    }
    
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