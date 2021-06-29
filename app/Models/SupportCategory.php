<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportCategory extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'name',
    ];

    protected $fillable = [
        'name',
        'order_number'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function questions() {
        return $this->hasMany('App\Models\SupportQuestion', 'category_id', 'id')->orderBy('order_number', 'asc');
    }
}

class SupportCategoryTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'support_category_id',
        'name',
    ];
}

?>