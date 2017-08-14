$(document).ready(function(){

	/*
	$('.order-pills a').click( function() {
    	$('#search-order').val( $(this).attr('data-val') );
    	$('.order-pills li').removeClass('active');
    	$(this).closest('li').addClass('active');
    } );
    */

    $('.search-more').click( function() {
    	$(this).closest('ul').find('li').removeClass('hidden');
    	$(this).parent().remove();
    } );


	function infinity_scroll() {

		var content_end = $('.site-content').offset().top + $('.site-content').outerHeight();
		var screen_bottom = $(window).scrollTop() + $(window).height();

		if( content_end - $(window).height()/2 < screen_bottom ) {
			if (ajax_is_running || end) {
				return;
			}
			ajax_is_running = true;
			page_num++;
	        $('#loading').show();

			$.ajax({
	            url     : '/' + lang + '/dentists/p/' + page_num + window.location.search ,
	            type    : 'get',
	            success : function( res ) {

	            	if (res.length) {
	            		$('#dentists-list .main-panel-body').append(res);
	            	} else {
	            		end = true;
	            		$('#loading').hide();
	            		$('#end-page').show();
	            	}
					ajax_is_running = false;
				},
	            error : function( data ) {
	            	$('#loading').hide();
					ajax_is_running = false;
				}
			});
		}
		
	}
	$(window).scroll(infinity_scroll);

});