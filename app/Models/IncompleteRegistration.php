<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncompleteRegistration extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'email',
        'email_public',
        'password',
        'mode',
        'name',
        'name_alternative',
        'address',
        'phone',
        'website',
        'country_id',
        'specialization',
        'photo',
        'photoThumb',
        'clinic_id',
        'clinic_name',
        'completed',
        'notified1',
        'notified2',
        'notified3',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    

    public function getSpecializationAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setSpecializationAttribute($value) {
        $this->attributes['specialization'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['specialization'] = implode(',', $value);            
        }
    }
    
}


?>