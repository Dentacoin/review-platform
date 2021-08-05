<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {

	use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'paid_report_id',
        'email',
        'payment_method',
        'price',
        'company_name',
        'company_number',
        'country_id',
        'address',
        'vat',
        'is_send'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

	public function user() {
	    return $this->hasOne('App\Models\User', 'id', 'user_id');
	}

	public function report() {
	    return $this->hasOne('App\Models\PaidReport', 'id', 'paid_report_id')->withTrashed();
	}
}

?>