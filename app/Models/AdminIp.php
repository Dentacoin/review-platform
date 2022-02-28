<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminIp extends Model {

	use SoftDeletes;
        
    protected $fillable = [
        'ip',
        'comment'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public static function getAdminIps($clearCache=false) {

        // dd(cache('admin-ips'));
		$ips = cache('admin-ips');
		if(empty($ips) || $clearCache ) {
			$ips = self::get()->pluck('ip')->toArray();

            cache([
            	'admin-ips' => $ips
            ], 86400);

		}

		return $ips;
	}
}

?>