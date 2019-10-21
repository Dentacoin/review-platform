var handleSorts;
var ajax_is_running = false;

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

		window.location.hash = $(this).attr('sort')+( $(this).attr('sort')!='featured' && $(this).attr('sort')!='untaken' && $(this).attr('sort')!='all' && $(this).attr('sort')!='taken' ? '-'+($(this).hasClass('order-asc') ? 'asc' : 'desc') : '' )

		handleSorts();
		surveyTitleHeight();
	} );

	$('.filter-menu a').click( function() {
		$('.filter-menu a').removeClass('active');
		$(this).addClass('active');

		handleSorts();
		surveyTitleHeight();
	});

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
		$('.swiper-slide[found="1"]:not(:visible)').each( function() {
			i++;
			if(i<=6) {
				$(this).show();
			}
		} );

		if( !$('.swiper-slide[found="1"]:not(:visible)').length ) {
			$(this).hide();
		}

		surveyTitleHeight();

	} );


	$('.scroll-to-surveys').click( function() {
		$('html, body').animate({
        	scrollTop: $('#strength-parent').offset().top
        }, 500);
	});

	$('input[name="target"]').change( function() {
		$(this).closest('.modern-radios').removeClass('has-error');
        $('.ajax-alert[error="'+$(this).attr('name')+'"]').remove();
        var val = $('#target-specific:checked').length;
        if(val) {
            $('.target-row').show();
        } else {
            $('.target-row').hide();
        }
    } );

    if ($('.select2').length) {
    	$(".select2").select2({
			multiple: true,
			placeholder: 'Select Country/ies',
		});
    }

    $('#request-survey-form').submit( function(e) {
        e.preventDefault();

        if (!$(this).find('.button').hasClass('disabled')) {

	        $(this).find('.ajax-alert').remove();
	        $(this).find('.alert').hide();
	        $(this).find('.has-error').removeClass('has-error');

	        if(ajax_is_running) {
	            return;
	        }
	        ajax_is_running = true;

	        var that = $(this);

	        // var countries = [];
	        // for (var i in $('[name="target-countries"]').select2('data')) {
	        // 	countries.push($('[name="target-countries"]').select2('data')[i]['text']);
	        // }

	        // $('[name="target-countries"]').val(countries.toString());
	        // console.log(countries.toString(), $('[name="target-countries"]').val());

	        //console.log($('[name="target-countries"]').val());
	        $.post( 
	            $(this).attr('action'), 
	            $(this).serialize() , 
	            (function( data ) {
	                if(data.success) {
	                	that[0].reset();
	                	$('.select2').val(null).trigger('change').select2();
	                   $(this).find('.alert-success').show();
	                } else {
	                    for(var i in data.messages) {
	                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');

	                        $('[name="'+i+'"]').addClass('has-error');

	                        if ($('[name="'+i+'"]').closest('.modern-radios').length) {
	                            $('[name="'+i+'"]').closest('.modern-radios').addClass('has-error');
	                        }
	                    }

	                    $('html, body').animate({
			                scrollTop: $('.ajax-alert:visible').first().offset().top - $('header').height() - 150
			            }, 500);
	                }
	                ajax_is_running = false;
	            }).bind(that), "json"
	        );          


	        return false;
	    }
	} );

});