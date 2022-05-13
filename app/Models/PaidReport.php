<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Image;

class PaidReport extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'slug',
        'title',
        'main_title',
        'checklists',
        'short_description',
        'table_contents',
        'methodology',
        'summary',
        'table_contents',
    ];

    protected $fillable = [
        'slug',
        'title',
        'main_title',
        'checklists',
        'languages',
        'download_format',
        'pages_count',
        'short_description',
        'table_contents',
        'methodology',
        'summary',
        'price',
        'hasimage',
        'table_contents',
    ];

    protected $dates = [
        'created_at',
        'launched_at',
        'updated_at',
        'deleted_at'
    ];

	public static $langs = [
		'en' => 'English',
	];

	public static $statuses = [
        'draft' => 'Draft',
        'published' => 'Published',
	];
    
	public static $formats = [
        'pdf' => 'PDF',
	];

    public function photos() {
        return $this->hasMany('App\Models\PaidReportPhoto', 'paid_report_id', 'id');
    }

    //single, social
    public function getImageUrl($type = 'single') {
        return $this->hasimage ? url('/storage/paid-reports/'.($this->id%100).'/'.$this->id.'-'.$type.'.png').'?rev=1'.$this->updated_at->timestamp : url('new-vox-img/stats-dummy.png');
    }

    public function getImagePath($type = 'single') {
        $folder = storage_path().'/app/public/paid-reports/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'-'.$type.'.png';
    }

    public function addImage($img, $type='single') {
        
        $extensions = ['image/jpeg', 'image/png'];

        if (in_array($img->mime(), $extensions)) {

            $to = $this->getImagePath($type);
            $img->resize(610, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($to);
            $this->hasimage = true;
            $this->save();
        }
    }

    public function setLanguagesAttribute($value) {
        $this->attributes['languages'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['languages'] = implode(',', $value);            
        }
    }
    
    public function getLanguagesAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }

    public function setDownloadFormatAttribute($value) {
        $this->attributes['download_format'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['download_format'] = implode(',', $value);            
        }
    }
    
    public function getDownloadFormatAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
}

class PaidReportTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'slug',
        'title',
        'main_title',
        'checklists',
        'short_description',
        'table_contents',
        'methodology',
        'summary',
    ];

    protected $casts = [
        'table_contents' => 'array',
    ];
}

?>