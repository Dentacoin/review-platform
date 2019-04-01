<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;

use App\Models\City;

class Country extends Model
{
    use SoftDeletes, Translatable;

    protected static function boot()
    {
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