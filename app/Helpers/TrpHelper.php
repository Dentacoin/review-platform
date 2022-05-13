<?php

namespace App\Helpers;

use App\Models\User;

class TrpHelper {

    public static function searchDentistsByName($searchSplitedUsername) {

        $dentistsAndClinics = User::where('is_dentist', true)
		->where(function($query) use ($searchSplitedUsername) {
			foreach ($searchSplitedUsername as $value) {
				$query->where(function($q) use ($value) {
					$q->orWhere('name', 'like', "%{$value}%")
					->orWhere('name_alternative', 'like', "%{$value}%");
				});
			}
		})->whereIn('status', config('dentist-statuses.shown'))
		->whereNull('self_deleted')
		->orderBy('is_partner', 'desc');

        return $dentistsAndClinics;
    }    
}