<?php

	function getLangUrl($path=false, $locale=null){
    	$locale = $locale ? $locale : \App::getLocale();
    	$locale = $locale == 'bg' ? 'ne' : $locale;

 		if(!$path){
  			return url($locale);
 		} else {
  			return url($locale."/".$path);
 		}
	}

	function getStarWidth($rate) {
		$stars = $rate * 30;
        $emptyspace = floor($rate) * 18;
        return $stars + $emptyspace;
	}