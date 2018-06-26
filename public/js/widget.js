var TRPWidget = TRPWidget || (function(){
    var _args = {}; // private

    return {
        init : function(url) {
        	if(url && url.indexOf('reviews.dentacoin.com')!=-1) {
	            var xhr = new XMLHttpRequest();
				xhr.open('GET', url);
				xhr.onload = function() {
				    if (xhr.status === 200) {
				    	//console.log(xhr.responseText);
				    	document.getElementById('trp-widget').innerHTML = xhr.responseText;
				    }
				    else {
				    }
				};
				xhr.send();        		
        	}
        }
    };
}());