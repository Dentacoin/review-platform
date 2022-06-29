<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBranch extends Model {
        
    protected $fillable = [
        'clinic_id',
        'branch_clinic_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function clinic() {
        return $this->hasOne('App\Models\User', 'id', 'clinic_id');
    }

    public function branchClinic() {
        return $this->hasOne('App\Models\User', 'id', 'branch_clinic_id');
    }
}

?>