var handleSorts;
var ajax_is_running = false;
var slice = 1;
var daysCountdown;

$(document).ready(function(){

	if($(window).outerWidth() > 768) {
		$('.another-questions .sort-menu').children().each( function() {
			$(this).text($(this).attr('desktop-val'));
		});
	}

	$('.sort-menu a').click( function() {
		if (!$(this).hasClass('active')) {
			$('.sort-menu a').removeClass('active');
			$(this).addClass('active');
		} else {
			$(this).toggleClass('order-asc');
			console.log('ee');
		}
		var sort_order = $(this).attr('sort')+'-'+($(this).hasClass('order-asc') ? 'asc' : 'desc' );
		$('[name="sortable_items"]').val(sort_order);
		$('[type="submit"]').trigger('click');

		$.ajax( {
			url: '/en/voxes-sort/',
			type: 'POST',
			data: {
				sort: sort_order
			},
			dataType: 'json',
		});
	} );

	if(user_id) {
		$.ajax( {
			url: '/en/voxes-sort/',
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				console.log(data.sort);
				if(data.sort) {

					var parts = data.sort.substring(1).split('-');
					if($('a[sort="'+parts[0]+'"]').length) {
						$('a[sort="'+parts[0]+'"]').trigger( "click" );			
						if(parts[1] && parts[1]=='asc') {
							$('a[sort="'+parts[0]+'"]').trigger( "click" );			
						}
					}
				}
			}
		});
	}

	var afterSubmitActions = function() {
		$('.survey-cats .survey-cat').click( function() {
			$('#surveys-categories').val( $(this).attr('cat-id') ).trigger('change');
		} );
	}
	afterSubmitActions();

	$('#surveys-categories').on('change', function() {
		$('[type="submit"]').trigger('click');
	});

	$('.filter_item').change( function(e) {
		e.preventDefault();
		$('.filter-menu label').removeClass('active');
		$(this).closest('label').addClass('active');
		$('[type="submit"]').trigger('click');
	});

	$('.another-questions').submit( function(e) {
		e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

		var formData = new FormData(this);
		formData.append("slice", 1);
		
        $.ajax({
            type: "POST",
            url: '/en/voxes-get/',
            success: function (data) {
            	$('#questions-inner').find('.home-vox').remove();
                if(data) {
					$('#survey-not-found').hide();
					$('#questions-inner').append(data);

					if($('.home-vox').length < 6) {
						$('#survey-more').hide();
					} else {
						$('#survey-more').show();
					}
				} else {
					if(!$('.home-vox').length) {
						$('#survey-not-found').show();
					}

					$('#survey-more').hide();
				}
				afterSubmitActions();
            },
            error: function (error) {
                console.log('error');
            },
            async: true,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });

        ajax_is_running = false;
	});

	$('.pagination a').on('click', function(e){
	    e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;
	    slice++;

		var formData = new FormData($('.another-questions')[0]);
		formData.append("slice", slice);

        $.ajax({
            type: "POST",
            url: '/en/voxes-get/',
            success: function (data) {
                if(data) {
					$('#survey-not-found').hide();
					var last_el = $('#questions-inner').find('.swiper-slide').last();
					$('#questions-inner').append(data);

					if($('.home-vox').length < 6) {
						$('#survey-more').hide();
					} else {
						$('#survey-more').show();
					}
					$('html, body').animate({
			        	scrollTop: last_el.next().offset().top
			        }, 500);
				} else {
					if(!$('.home-vox').length) {
						$('#survey-not-found').show();
					}
					$('#survey-more').hide();
				}
				afterSubmitActions();
            },
            error: function (error) {
                console.log('error');
            },
            async: true,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });
        ajax_is_running = false;
	});

	if(!$('.pagination').length || !$('.pagination li').length ) {
		$('#survey-more').hide();
	}

	$('#survey-more').click( function() {
		$('.pagination li').last().find('a')[0].click();
	} );

	$('.scroll-to-surveys').click( function() {
		$('html, body').animate({
        	scrollTop: $('#strength-parent').offset().top
        }, 500);
	});

	if($('.flickity').length) {
		$('.flickity').flickity({
			autoPlay: false,
			wrapAround: true,
			pageDots: false,
			groupCells: 1,
			cellAlign: $(window).outerWidth()<768 ? 'center' : 'left',
			freeScroll: false,
			contain: true,
			on: {
				ready: fixFlickty,
			}
		});

		var fixFlickty = function() {
			if ($('.slider-posts').length) {
				$('.slider-posts').each( function() {
					var mh = 0;
					$(this).find('.post .post-inner').css('height', 'auto');
					$(this).find('.post .post-inner').each( function() {
						if( $(this).height() > mh ) {
							mh = $(this).height();
						}
					} );
					$(this).find('.post .post-inner').css('height', mh+'px');
				} );
			}

			if ($('.flickity-slider').length) {
				$('.flickity-slider').each( function() {

					var mh = 0;
					$(this).find('.slider-wrapper').css('height', 'auto');
					$(this).find('.slider-wrapper').each( function() {
						if( $(this).height() > mh ) {
							mh = $(this).height();
						}
					} );
					$(this).find('.slider-wrapper').css('height', mh+'px');
				} );
			}
		}
		$(window).resize( fixFlickty );
		fixFlickty();
	}

	if($('.hours-countdown').length) {

		var seconds = parseInt($('.hours-countdown').text());
		function timer() {
			var days        = Math.floor(seconds/24/60/60);
			var hoursLeft   = Math.floor((seconds) - (days*86400));
			var hours       = Math.floor(hoursLeft/3600);
			var minutesLeft = Math.floor((hoursLeft) - (hours*3600));
			var minutes     = Math.floor(minutesLeft/60);
			var remainingSeconds = seconds % 60;
			function pad(n) {
				return (n);
				// return (n < 10 ? "0" + n : n);
			}

			console.log(days);
			$('.hours-countdown').html((days != 0 ? (pad(days) + " DAYS ") : '') + pad(hours) + " HOURS " + pad(minutes) + " MIN " + pad(remainingSeconds) + " SEC");
			$('.vip-access-wrapper').css('display', 'flex');

			if (seconds == 0) {
				clearInterval(daysCountdown);
				$('.vip-access-wrapper').hide();
			} else {
				seconds--;
			}
		}
		var daysCountdown = setInterval(timer, 1000);
	}

});