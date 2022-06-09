$(document).ready(function() {

    if( $('#widget-carousel').length ) {
        $(".select-me").on("click focus", function () {
            $(this).select();
        });

        $(".copy-widget").click(function(){
            $(this).closest('.widget-code-wrap').find('textarea').select();
            document.execCommand('copy');
        });

        $('.widget-tabs .col, \
        #select-reviews-popup input, \
        #popup-widget input, \
        #popup-widget select:not(#dentist-page)').on('click change keyup keypress', function(e) {
            if(typeof widet_url=='undefined') {
                return;
            }
            var layout = $('[name="widget-layout"]:checked').val();
            var image_url = $('[name="widget-layout"]:checked').closest('.radio-label').find('img').attr('src');
            if ($('[name="widget-layout"]:checked').val() == 'carousel' && parseInt($('[name="slide-results"]').val()) == 3) {
                $('#selected-image-layout').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-carousel-3.png');
            } else if($('[name="widget-layout"]:checked').val() == 'badge' && $('[name="badge"]').val() == 'mini') {
                $('#selected-image-layout').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-badge-min.png');
            } else {
                $('#selected-image-layout').attr('src', image_url);
            }

            if (parseInt($('[name="slide-results"]').val()) == 3) {
                $('#widget-carousel').closest('label').find('img').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-carousel-3.png');
            } else {
                $('#widget-carousel').closest('label').find('img').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-carousel.png');
            }

            if ($('[name="badge"]').val() == 'mini') {
                $('#widget-badge').closest('label').find('img').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-badge-min.png');
            } else {
                $('#widget-badge').closest('label').find('img').attr('src', 'https://reviews.dentacoin.com/img-trp/widget-badge.png');
            }
            
            
            var getParams = '?layout='+$('[name="widget-layout"]:checked').val();
            $('#selected-layout').html($('[name="widget-layout"]:checked').closest('label').find('p').attr('layout-text'));
            var custom_width = false;
            var custom_heigth = false;

            $('.select-reviews').show();
            
            if (layout == 'carousel') {
                if ($('[name="slide-results"][cant-select]').length && parseInt($('[name="slide-results"]').val()) == 3) {
                    getParams += '&slide=1';
                    $('[name="slide-results"]').val('1');
                    $('.slider-alert').show();
                } else {
                    getParams += '&slide='+$('[name="slide-results"]').val();
                }
            } else if(layout == 'list') {
                getParams += '&width='+$('[name="list-width"]').val()+'&height='+$('[name="list-height"]').val();
                custom_heigth = true;

                if (parseInt($('[name="list-width"]').val()) != 100) {
                    custom_width = true;
                }
                $('.slider-alert').hide();
            } else if(layout == 'badge') {
                getParams += '&badge='+$('[name="badge"]').val();
                $('.select-reviews').hide();
                $('.slider-alert').hide();
            }

            if ($('[name="widget-layout"]:checked').val() == 'carousel' && parseInt($('[name="slide-results"]').val()) == 3 && $('#trusted-chosen').attr('trusted-reviews-count') < 4) {
                $('#trusted-chosen').hide();
            } else if($('#trusted-chosen').attr('trusted-reviews-count') < 1) {
                $('#trusted-chosen').hide();
            } else {
                $('#trusted-chosen').show();
            }

            if (layout != 'badge') {
                
                getParams += '&review-type='+$('[name="review-type"]:checked').val();

                if ($('[name="review-type"]:checked').val() == 'all') {
                    getParams += '&review-all-count='+$('[name="all-reviews-option"]:checked').val();
                } else if($('[name="review-type"]:checked').val() == 'trusted') {
                    getParams += '&review-trusted-count='+$('[name="trusted-reviews-option"]:checked').val();
                } else if($('[name="review-type"]:checked').val() == 'custom') {
                    if ($('[name="widget-custom-review"]:checked').length) {
                        $('[name="widget-custom-review"]:checked').each( function() {
                            getParams += '&review-custom[]='+$(this).val();
                        });
                    }
                }

            }

            if (!$('#widget-option-flexible:visible').length && custom_width) {
                $('.widget-tab-alert').show();
            } else {
                $('.widget-tab-alert').hide();
            }

            $('#custom-reviews-length').html($('[name="widget-custom-review"]:checked').length)
            
            var parsedUrl = widet_url+getParams;

            if (!$(e.target).closest('.popup-tabs').length) {
                $('.get-widget-code-wrap').show();
                $('.widget-last-step').hide();
            }

            if(layout == 'badge') {
                $('.get-widget-code-wrap').hide();
                $('.widget-last-step').show();
            }

            if(layout == 'fb') {
                $('.widget-last-step').hide();
            }

            $('.widget-custom-reviews-alert').hide();

            var iframe_url = parsedUrl.replace('&width=','&customwidth=').replace('&height=','&customheight=');
            $('#option-iframe textarea').val('<!--Trusted Reviews Widget-->\n\r<iframe style="width: 100%; height: '+(custom_heigth ? $('[name="list-height"]').val()+'px' : (layout == 'carousel' ? '750px' : '50vh'))+'; border: none; outline: none;" src="'+iframe_url+'"></iframe>\n\r<!--End Trusted Reviews Widget-->');
            $('#option-js textarea').val('<!--Trusted Reviews Widget-->\n\r<div id="trp-widget"></div><script type="text/javascript" src="https://reviews.dentacoin.com/js-trp/widget.js"></script> <script type="text/javascript"> TRPWidget.init("'+parsedUrl+'"); </script>\n\r<!--End Trusted Reviews Widget-->');
        });
        $('#widget-carousel').trigger('change');
    }
    

    $('.get-widget-code').click( function() {
        if($('[name="review-type"]:checked').val() == 'custom' && !$('[name="widget-custom-review"]:checked').length) {
            $('.widget-custom-reviews-alert').show();
        } else if ($('[name="widget-layout"]:checked').val() == 'carousel' && parseInt($('[name="slide-results"]').val()) == 3 && $('[name="review-type"]:checked').val() == 'custom' && $('[name="widget-custom-review"]:checked').length < 4) {
            $('.widget-custom-reviews-alert').show();
        } else {

            $(this).closest('.get-widget-code-wrap').hide();
            $('.widget-last-step').show();

            var selected_layout = $('[name="widget-layout"]:checked').val();
            gtag('event', 'Code', {
                'event_category': 'Widgets',
                'event_label': selected_layout,
            });

            if($('body').hasClass('guided-tour')) {
                $('.next-tour-step').trigger('click');
            }
        }
    });
    

    $('.widget-button').click( function() {
        $(this).closest('.widget-step').hide();
        $('.widget-step-'+$(this).attr('to-step')).show();
        if(!$('body').hasClass('reviews-guided-tour')) {

            $('.popup.active').animate({
                scrollTop: $('.popup.active').offset().top
            }, 200);
        }

        if( $(this).hasClass('widget-layout-button')) {
            var selected_layout = $('[name="widget-layout"]:checked').val();

            gtag('event', 'Layout', {
                'event_category': 'Widgets',
                'event_label': selected_layout,
            });

            if(selected_layout == 'badge') {

                gtag('event', 'Code', {
                    'event_category': 'Widgets',
                    'event_label': selected_layout,
                });
            }

            if (selected_layout == 'fb') {
                $('.show-fb').show();
                $('.hide-fb').hide();
            } else {
                $('.show-fb').hide();
                $('.hide-fb').show();
            }
        }
    });

    $('.open-hidden-option').click( function() {
        $(this).closest('label').find('.hidden-option').toggleClass('active');
        $(this).toggleClass('active');
    });

    $('.type-radio-widget').change( function(e) {
        $(this).closest('.option-checkboxes').find('label').removeClass('active');
        $(this).closest('label').addClass('active');
    });

    $('.type-radio-widget-first').change( function(e) {
        $(this).closest('.select-reviews').find('.hidden-option').removeClass('active');
        $(this).closest('.radio-label').find('.hidden-option').addClass('active');
        $(this).closest('.modern-radios').find('.first-label').removeClass('active');
        $(this).closest('.first-label').addClass('active');
        $(this).closest('.first-label').toggleClass('open');
    });

    $('.add-widget-button').click( function() {
        gtag('event', 'Open', {
            'event_category': 'Widgets',
            'event_label': 'Popup',
        });
    });

    $('.widget-tabs a').click( function() {
        $('.widget-tabs a').removeClass('active');
        $(this).addClass('active');
        $('.widget-content').hide();
        $('#widget-option-'+$(this).attr('data-widget')).show();
    });

    $('.form-fb-tab').submit( function(e) {
        e.preventDefault();

        if($('[name="review-type"]:checked').val() == 'custom' && !$('[name="widget-custom-review"]:checked').length) {
            $('.widget-custom-reviews-fb-alert').show();
            return;
        }

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $(this).find('.fbtab-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        var that = $(this);

        var reviews_cust = [];
        if($('[name="review-type"]:checked').val() == 'custom') {
            $('[name="widget-custom-review"]:checked').each( function() {
                reviews_cust.push($(this).val());
            });
        }

        $.ajax({
            type: "POST",
            url: that.attr('action'),
            data: {
                page: $('#dentist-page').val(),
                reviews_type: $('[name="review-type"]:checked').val(),
                all_reviews: $('[name="all-reviews-option"]:checked').val(),
                trusted_reviews: $('[name="trusted-reviews-option"]:checked').val(),
                custom_reviews: reviews_cust,
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(data) {
                if(data.success) {
                    $('.widget-step-1').show();
                    $('.widget-step-2').hide();
                    $('#popup-widget').removeClass('active');
                    $('#popup-widget').removeClass('active');
                    $('#facebook-tab-success').addClass('active');

                    // $('.form-fb-tab').find('.fbtab-alert').show().addClass('alert-success').html(data.message);

                    gtag('event', 'Done', {
                        'event_category': 'Widgets',
                        'event_label': 'FB Complete',
                    });
                } else {
                    $('.form-fb-tab').find('.fbtab-alert').show().addClass('alert-warning').html(data.message);                    
                }
                ajax_is_running = false;
            },
            error: function(data) {
                console.log('error');
            }
        });
    });

    $('.fb-tab-submit').click( function(e) {
        e.preventDefault();

        $('.ajax-alert').remove();
        $('.has-error').removeClass('has-error');

        if($('[name="review-type"]:checked').val() == 'custom' && !$('[name="widget-custom-review"]:checked').length) {
            $('.widget-custom-reviews-fb-alert').show();
            return;
        }

        if(!$('#fb-page-id').val()) {
            $('#fb-page-id').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="page">'+$('.facebook-tab').attr('error-missing')+'</div>');
            $('#fb-page-id').addClass('has-error');
            return;
        }

        if(!$.isNumeric($('#fb-page-id').val())) {
            $('#fb-page-id').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="page">'+$('.facebook-tab').attr('error-not-numeric')+'</div>');
            $('#fb-page-id').addClass('has-error');
            return;
        }

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);

        var reviews_cust = [];
        if($('[name="review-type"]:checked').val() == 'custom') {
            $('[name="widget-custom-review"]:checked').each( function() {
                reviews_cust.push($(this).val());
            });
        }

        $.ajax({
            type: "POST",
            url: that.attr('fb-url'),
            data: {
                page: $('#fb-page-id').val(),
                reviews_type: $('[name="review-type"]:checked').val(),
                all_reviews: $('[name="all-reviews-option"]:checked').val(),
                trusted_reviews: $('[name="trusted-reviews-option"]:checked').val(),
                custom_reviews: reviews_cust,
                _token: $('input[name="_token"]').val(),
            },
            dataType: 'json',
            success: function(data) {
                if(data.success) {
                    window.location.href = data.link;
                    // $('.widget-step-1').show();
                    // $('.widget-step-2').hide();
                    // $('#popup-widget').removeClass('active');
                    // $('#popup-widget').removeClass('active');
                    // $('#facebook-tab-success').addClass('active');

                    // $('.form-fb-tab').find('.fbtab-alert').show().addClass('alert-success').html(data.message);

                    gtag('event', 'Done', {
                        'event_category': 'Widgets',
                        'event_label': 'FB Complete',
                    });
                } else {
                    $('.form-fb-tab').find('.fbtab-alert').show().addClass('alert-warning').html(data.message);                    
                }
                ajax_is_running = false;
            },
            error: function(data) {
                console.log('error');
            }
        });
    });

    $('.close-select-widget-reviews-popup').click( function() {
        $('.popup').removeClass('active');
		$('body').removeClass('popup-visible');
		showPopup( 'popup-widget' );
    });
});