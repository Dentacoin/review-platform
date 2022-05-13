<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Continent extends Model {

    protected static function boot() {
        parent::boot();
     
        // Order by name ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('name', 'asc');
        });
    }

    protected $fillable = [
        "name",
        "slug",
    ];

    public function countries() {
        return $this->hasMany('App\Models\Country', 'continent_id', 'id')->orderBy('name', 'ASC');
    }
}