<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VoxToCategory;

class VoxCategory extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'name',
    ];

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;


    public function voxes() {
        return $this->hasMany('App\Models\VoxToCategory', 'vox_category_id', 'id')->with('vox')->whereHas('vox', function ($query) {
            $query->where('type', 'normal');
        });
    }

}

class VoxCategoryTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'name',
    ];

}

?>