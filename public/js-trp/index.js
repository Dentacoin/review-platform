var slider = null;
var sliderTO = null;
var displaySuggestions;
var sendSuggestions;
var handlePopups;

jQuery(document).ready(function($){

	if ($('.slider-wrapper').length <= 4 && $(window).outerWidth() > 998) {
		$('.flickity').addClass('flex');
	} else {
		$('.flickity').flickity({
			autoPlay: false,
			wrapAround: true,
			pageDots: false,
			groupCells: 1,
			cellAlign: $(window).width()<768 ? 'center' : 'left',
			freeScroll: false,
			contain: true,
			on: {
				ready: fixFlickty,
			}
		});
	}

	var fixFlicktyInner = function() {
		$('.flickity-slider').each( function() {
			var mh = 0;
			$(this).find('.slider-container').css('height', 'auto');
			$(this).find('.slider-container').each( function() {
				if( $(this).height() > mh ) {
					mh = $(this).height();
				}
			} );
			$(this).find('.slider-container').css('height', mh+'px');
		} );
	}
	fixFlicktyInner();

	$('.button-want-to-add-dentist').click( function() {
		$.ajax({
            type: "GET",
            url: lang + '/want-to-invite-dentist',
        });
	});

	if( window.location.hash == '#invite-form' ) {
        $('html, body').animate({
            scrollTop: $(".invite-new-dentist-wrapper").offset().top - $('.header').height()
        }, 500);
	}

	setTimeout( function() {
		$('.city-dentist').removeAttr('placeholder');
	},1000);


	$('#search-dentists-city').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);
        $(this).find('.alert-warning').hide();

        $.post( 
            window.location.href, 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	window.location.reload();
                } else {
                	that.find('.alert-warning').show();
                }
                ajax_is_running = false;

            }, "json"
        );
	});

	$(window).scroll( function() {
		if (!$('#to-append').hasClass('appended')) {
			$.ajax({
	            type: "GET",
	            url: lang + '/index-down/',
	            success: function(ret) {
	            	if (!$('#to-append').hasClass('appended')) {
	            		$('#to-append').append(ret);
	            		$('#to-append').addClass('appended');

	            		handlePopups();
	            	}
	                
	            },
	            error: function(ret) {
	                console.log('error');
	            }
	        });
		}
	});

});