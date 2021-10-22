<?php

namespace App\Helpers;

class AdminHelper {

    public static function paginationsFunction($total_pages, $adjacents, $page) {

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
            $start = 1;
            $end   = $total_pages;
        } else {
            if(($page - $adjacents) > 1) { 
                if(($page + $adjacents) < $total_pages) { 
                    $start = ($page - $adjacents);            
                    $end   = ($page + $adjacents);         
                } else {             
                    $start = ($total_pages - (1+($adjacents*2)));  
                    $end   = $total_pages;               
                }
            } else {               
                $start = 1;                                
                $end   = (1+($adjacents * 2));             
            }
        }

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

}