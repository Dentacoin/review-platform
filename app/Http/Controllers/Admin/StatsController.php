<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\AdminController;

use DB;

class StatsController extends AdminController {

    public function registrations( ) {
        $weeks = DB::select("
            SELECT 
                CONCAT(YEAR(`created_at`), 'W', REPLACE(LPAD( WEEK( DATE_SUB(  `created_at` , INTERVAL 16 HOUR ) , 5 ) , 2 , '0' ) , '00', '52') ) AS  `week`,
                SUM( IF(  `is_dentist` AND `platform` = 'trp', 1, 0 ) ) AS `dentist`, 
                SUM( IF(  !`is_dentist` AND `platform` = 'trp' , 1, 0 ) ) AS `patient`, 
                SUM( IF(  `platform` = 'vox' , 1, 0 ) ) AS `vox`,
                SUM( IF(  `platform` = 'dentacare' , 1, 0 ) ) AS `dentacare`,
                SUM( IF(  `platform` = 'assurance' , 1, 0 ) ) AS `assurance`,
                SUM( IF(  `platform` = 'dentacoin' , 1, 0 ) ) AS `dentacoin`,
                SUM( IF(  `platform` = 'dentists' , 1, 0 ) ) AS `dentists`
            FROM  `users` 
            GROUP BY `week` 
            ORDER BY `id` DESC
        ");

        $table = [];
        // dd($weeks);
        foreach ($weeks as $w) {
            $s = date('d.m.Y', strtotime($w->week) - 86400*3 );
            $e = date('d.m.Y', strtotime($w->week) + 86400*4 );
            $table[] = [
                'week' => $s.'-'.$e,
                'dentists' => $w->dentist,
                'patients' => $w->patient,
                'voxes' => $w->vox,
                'dentacare' => $w->dentacare,
                'assurance' => $w->assurance,
                'dentacoin' => $w->dentacoin,
                'dentists' => $w->dentists,
            ];
        }

        return $this->showView('stats', array(
            'name' => 'registrations',
            'table' => $table,
        ));

    }

}