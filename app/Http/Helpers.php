<?php

	function getVoxUrl($path=false, $locale=null){
    	//return getLangUrl($path);
    	return getLangUrl($path, $locale, 'https://dentavox.dentacoin.com/');
	}



	function getStarWidth($rate) {
		$stars = $rate * 30;
	    $emptyspace = floor($rate) * 18;
	    return $stars + $emptyspace;
	}

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

	function getLangRoute($path=false, $params = [], $locale=null){
		$params['locale'] = $locale ?? App::getLocale();
		return route($path, $params);
	}

	function getDentacoinHubApplications() {
		$fn = storage_path('dentacoin-platforms');
        $t = file_exists($fn) ? filemtime($fn) : null;
        if(!$t || $t < time()-300) {
            file_put_contents($fn, file_get_contents('https://dentacoin.com/info/applications'));
        }
        return json_decode(file_get_contents($fn));
	}