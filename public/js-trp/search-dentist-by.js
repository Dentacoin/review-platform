$(document).ready(function() {    
		
    $('#quick-search-country').on('keyup', function(e) {

        $('.countries-wrapper').removeClass('chosen-continent');
        $('.letters-country-section').show();
        $('.letters-country-section .country').show();
        $('.letters-country-section .country-button').show();
        $('.continent').removeClass('active');
        $('.continent.all-continents').addClass('active');
        $('.countries-letter').show().removeClass('active');
        $('.countries-letter.all-letters').addClass('active');
        // $('.dentists-countries-results .info').hide();
        
        if($(this).val()) {

            var searched_country = $(this);
            $('.letters-country-section .country-button').each( function() {
                if (
                    $(this).attr('country-name').toLowerCase().indexOf(searched_country.val().toLowerCase()) >= 0 
                    || ( typeof $(this).attr('country-second-name') !== 'undefined' && $(this).attr('country-second-name').toLowerCase().indexOf(searched_country.val().toLowerCase()) >= 0 )
                ) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            $('.letters-country-section .country').each( function() {
                if($(this).find('.country-button:visible').length == 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });

            $('.letters-country-section').each( function() {
                
                if($(this).find('.country-button:visible').length == 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });

            // if($('.dentists-countries-results-wrapper').children('.letters-country-section:visible').length == 0) {
            //     $('.dentists-countries-results .info').show();
            // } else {
            //     $('.dentists-countries-results .info').hide();
            // }
        } else {
            $('.letters-country-section').show();
            $('.letters-country-section .country').show();
            $('.letters-country-section .country-button').show();
        }
    });

    $('.countries-letter').click( function() {
        if($('#quick-search-country').val()) {
            $('#quick-search-country').val('').trigger('keyup');
        }
        if(!$(this).hasClass('active')) {
            if($(this).hasClass('all-letters')) {
                if($('.countries-wrapper').hasClass('chosen-continent')) {
                    $('.country-button[continent-id="'+$('.continent.active').attr('id')+'"]').closest('.letters-country-section').show();
                } else {
                    $('.letters-country-section').show();
                }
            } else {
                $('.letters-country-section').hide();
                $('.letters-country-section[letter="'+$(this).html()+'"]').show();
            }

            $('.countries-letter').removeClass('active');
            $(this).addClass('active');
        }
    });

    $('.continent').click( function() {
        if($('#quick-search-country').val()) {
            $('#quick-search-country').val('').trigger('keyup');
        }
        if(!$(this).hasClass('active')) {
            $('.letters-country-section').show();
            $('.countries-letter').removeClass('active');
            $('.countries-letter.all-letters').addClass('active');
            $('.countries-letter').show();
            
            if($(this).hasClass('all-continents')) {
                $('.countries-wrapper').removeClass('chosen-continent');
                $('.country-button').show();
            } else {
                $('.countries-wrapper').addClass('chosen-continent');
                $('.continent-title-wrapper .continent-title').html($(this).html());
                $('.continent-title-wrapper .continent-dentists span').html($(this).attr('dentists-count'));
                $('.country-button').hide();
                $('.country-button[continent-id="'+$(this).attr('id')+'"]').show();

                $('.letters-country-section').each( function() {
                    // console.log($(this).find('.country-button:visible').length);
                    if($(this).find('.country-button:visible').length == 0) {
						$(this).hide();
                        $('.countries-letter[letter="'+$(this).attr('letter')+'"]').hide();
					} else {
						$(this).show();
                        $('.countries-letter[letter="'+$(this).attr('letter')+'"]').show();
					}
                });
            }
            
            $('.continent').removeClass('active');
            $(this).addClass('active');
        }
    });

    $('.scroll-up').click( function() {
		$('html, body').animate({scrollTop: '0px'}, 300);
	});

    $(window).scroll(function() {
        if($(window).scrollTop() > 100) {
			$('.scroll-up').css('opacity', 1);
		} else {
			$('.scroll-up').css('opacity', 0);
		}
    });

    $('.open-country-filters').click( function() {
        $('.hidden-mobile-filters').addClass('open');
    });

    $('.close-filter').click( function() {
        $('.hidden-mobile-filters').removeClass('open');
    });
});