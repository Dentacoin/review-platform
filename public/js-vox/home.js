var handleSorts;
var ajax_is_running = false;
var slice = 0;

$(document).ready(function(){

	var setupPagination = function() {
		$('#survey-more').hide();
		var total = $('.swiper-slide:visible').length;
		if(total>6) {
			var i=0;
			$('.swiper-slide:visible').each( function() {
				i++;
				if(i>6) {
					$(this).hide();
				}
			} );
			$('#survey-more').show();
		}
	}

	handleSorts = function() {
		// if(!$('.sort-menu a.active').length) {
		// 	return;
		// }

		//$('#surveys-categories').val('all');
		//$('#survey-search').val('');
		$('.swiper-slide').show().attr("found", 1);

		if ($('#survey-search').val().length > 3) {
			$('.swiper-slide').each( function() {
				if( $(this).find('.survey-title').text().toLowerCase().indexOf($('#survey-search').val().toLowerCase()) == -1) {
					$(this).hide().attr("found", 0);
				}
			});
		}

		if( $('#surveys-categories').val() != 'all' ) {
			$('.swiper-slide').each( function() {
				if(!$(this).find('.survey-cat[cat-id="'+ $('#surveys-categories').val() +'"]').length) {
					$(this).hide().attr("found", 0);
				}
			});
		}

		if($('.filter-menu a.active').attr('filter') == 'taken') {
			$('.questions-inner .swiper-slide[taken="0"]').hide().attr("found", 0);
		} else if($('.filter-menu a.active').attr('filter') == 'untaken') {
			$('.questions-inner .swiper-slide[taken="1"]').hide().attr("found", 0);
		}

		var sort = $('.sort-menu a.active').attr('sort');
		var sort_element = $('.sort-menu a.active');
		var wrapper = $('.questions-inner');
		var list = wrapper.find('.home-vox');
		var request_survey = $('.request-vox');

		var order_multiplier = sort=='newest' ? (sort_element.hasClass('order-asc') ? -1 : 1) : (sort_element.hasClass('order-asc') ? 1 : -1);

		if(sort=='newest') {
			list.sort(function(a, b) {
				if( parseInt($(a).attr('featured')) > parseInt($(b).attr('featured')) ) {
                    return -1;
                } else if( parseInt($(a).attr('featured')) < parseInt($(b).attr('featured')) ) {
                    return 1;
                } else {
                    return (parseInt($(a).attr('sort-order')) < parseInt($(b).attr('sort-order')) ? -1 : 1) * order_multiplier;
                }
			});
		} else if(sort=='popular') {
			list.sort(function(a, b) {
				if( parseInt($(a).attr('featured')) > parseInt($(b).attr('featured')) ) {
                    return -1;
                } else if( parseInt($(a).attr('featured')) < parseInt($(b).attr('featured')) ) {
                    return 1;
                } else {
                    return (parseInt($(a).attr('popular')) < parseInt($(b).attr('popular')) ? -1 : 1) * order_multiplier;
                }
			});
		} else if(sort=='reward') {
			list.sort(function(a, b) {
				if( parseInt($(a).attr('featured')) > parseInt($(b).attr('featured')) ) {
                    return -1;
                } else if( parseInt($(a).attr('featured')) < parseInt($(b).attr('featured')) ) {
                    return 1;
                } else {
                    return (parseInt($(a).attr('dcn')) < parseInt($(b).attr('dcn')) ? -1 : 1) * order_multiplier;
                }
			});
		} else if(sort=='duration') {
			list.sort(function(a, b) {
				if( parseInt($(a).attr('featured')) > parseInt($(b).attr('featured')) ) {
                    return -1;
                } else if( parseInt($(a).attr('featured')) < parseInt($(b).attr('featured')) ) {
                    return 1;
                } else {
                    return (parseInt($(a).attr('duration')) < parseInt($(b).attr('duration')) ? -1 : 1) * order_multiplier;
                }
			});
		}

		// list.each( function() {
		// 	if ($(this).attr('taken')=='1') {
		// 		$(this).hide().attr("found", 0);
		// 	}
		// });

		wrapper.append(request_survey);
		list.each(function() {
		    wrapper.append(this);
		});

		if ( !$('.swiper-slide:visible').length ) {
			$('#survey-not-found').show();
		} else {
			$('#survey-not-found').hide();
			setupPagination();
		}
	}
	handleSorts();

	if($(window).outerWidth() <= 768) {
		$('.another-questions .sort-menu').children().each( function() {
			if($(this).text().split(" ").length > 1) {
				$(this).text($(this).text().split(" ")[1]);
			}
		});
	}

	var surveyTitleHeight = function() {
		if(window.innerWidth >= 1200) {
			$('.another-questions .swiper-slide').each( function() {
				if( $(this).find('h4').outerHeight() > 30) {
					$(this).find('.vox-description').css('max-height', (window.innerWidth >= 1600 ? '64px' : '54px'));
				}
			});
		}
	}
	$(window).resize(surveyTitleHeight);
	surveyTitleHeight();

	$('.sort-menu a').click( function() {
		if (!$(this).hasClass('active')) {
			$('.sort-menu a').removeClass('active');
			$(this).addClass('active');
		} else {
			$(this).toggleClass('order-asc');
		}

		window.location.hash = $(this).attr('sort')+( $(this).attr('sort')!='featured' && $(this).attr('sort')!='untaken' && $(this).attr('sort')!='all' && $(this).attr('sort')!='taken' ? '-'+($(this).hasClass('order-asc') ? 'asc' : 'desc') : '' );

		$.ajax( {
			url: '/en/voxes-sort',
			type: 'POST',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'),
				sort: window.location.hash
			},
			dataType: 'json',
		});

		handleSorts();
		surveyTitleHeight();
	} );

	$('.filter-menu a').click( function() {
		$('.filter-menu a').removeClass('active');
		$(this).addClass('active');

		handleSorts();
		surveyTitleHeight();
	});

	if(user_id) {
		$.ajax( {
			url: '/en/voxes-sort',
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				
				if(data.sort) {

					var parts = data.sort.substring(1).split('-');
					if($('a[sort="'+parts[0]+'"]').length) {
						$('a[sort="'+parts[0]+'"]').trigger( "click" );			
						if(parts[1] && parts[1]=='asc') {
							$('a[sort="'+parts[0]+'"]').trigger( "click" );			
						}
					}
				} else {

					if (window.location.hash.length) {
						var parts = window.location.hash.substring(1).split('-');
						if($('a[sort="'+parts[0]+'"]').length) {
							$('a[sort="'+parts[0]+'"]').trigger( "click" );			
							if(parts[1] && parts[1]=='asc') {
								$('a[sort="'+parts[0]+'"]').trigger( "click" );			
							}
						}
					} else {
						// $('.sort-menu a').first().trigger('click');
					}
				}
			}
		});
	}

	$('#survey-search').on('change keyup', function() {
		handleSorts();
	});

	$('#surveys-categories').on('change', function() {
		handleSorts();
	});

	$('.survey-cats .survey-cat').click( function() {
		$('#surveys-categories').val( $(this).attr('cat-id') ).trigger('change');
	} );

	$('#survey-more').click( function() {
		var i=0;
		var survey = null;
		$('.swiper-slide[found="1"]:not(:visible)').each( function() {
			i++;
			if(i<=6) {
				if(i == 1) {
					survey = $(this);
				}
				$(this).show();
			}
		} );

		if( !$('.swiper-slide[found="1"]:not(:visible)').length ) {
			$(this).hide();
		}

		surveyTitleHeight();

		if(survey) {
			$('html, body').animate({
	        	scrollTop: survey.offset().top
	        }, 500);
		}

		// slice++;

		// $.ajax( {
		// 	url: '/en/voxes-get',
		// 	type: 'POST',
		// 	data: {
		// 		// _token: $('meta[name="csrf-token"]').attr('content'),
		// 		sort: window.location.hash,
		// 		slice: slice
		// 	},
		// 	success: function(data) {
		// 		if(data) {

		// 			var last_el = $('#questions-inner').find('.swiper-slide').last();
		// 			$('#questions-inner').append(data);
		// 			surveyTitleHeight();
		// 			$('html, body').animate({
		// 	        	scrollTop: last_el.next().offset().top
		// 	        }, 500);
		// 		} else {
		// 			$('#survey-more').hide();
		// 		}
		// 	}
		// });

	} );

	$('.scroll-to-surveys').click( function() {
		$('html, body').animate({
        	scrollTop: $('#strength-parent').offset().top
        }, 500);
	});

});