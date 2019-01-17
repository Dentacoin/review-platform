<?php

	function getLangUrl($path=false, $locale=null){
    $locale = $locale ? $locale : \App::getLocale();

 		if(!$path || $path == '/' ){
  			return url($locale == 'en' ? '/' : $locale)."/";
 		} else {
  			return url($locale."/".$path)."/";
 		}
	}

	function getStarWidth($rate) {
		$stars = $rate * 30;
    $emptyspace = floor($rate) * 18;
    return $stars + $emptyspace;
	}