<?php

	function getLangUrl($path=false, $locale=null, $domain=null){
    	$locale = $locale ? $locale : \App::getLocale();

 		if(!$path || $path == '/' || $path == 'index' ){
  			$link =  $locale == 'en' ? '/' : $locale;
 		} else {
  			$link = $locale."/".$path;
 		}

 		if($domain) {
 			return $domain.$link.'/';
 		} else {
 			return url($link)."/"; 			
 		}

	}

	function getVoxUrl($path=false){
    	//return getLangUrl($path);
    	return getLangUrl($path, $locale, 'https://dentavox.dentacoin.com/');
	}



	function getStarWidth($rate) {
		$stars = $rate * 30;
	    $emptyspace = floor($rate) * 18;
	    return $stars + $emptyspace;
	}