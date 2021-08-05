var ajax_is_running = false;

$(document).ready(function(){

    $('.accordion-title').click( function() {
        $(this).closest('.accordion').toggleClass('active');
    });

    $('.invoice').click( function(e) {
        console.log('ree');
        e.preventDefault();
        $('.checkout-form').toggleClass('active');
    });

    $('.checkout-form').submit( function(e) {
        e.preventDefault();

        $(this).find('.ajax-alert').remove();
        $('.agree-error').hide();
        // $(this).find('.alert').hide();
        $(this).find('.has-error').removeClass('has-error');

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);
        
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                if(data.success) {
                    if(typeof data.link !== 'undefined') {

                        window.location.href = data.link;
                    } else {
                        $('.checkout-form').hide();
                        $('.invoice').hide();
                    }
                } else {
                    for(var i in data.messages) {
                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');

                        $('[name="'+i+'"]').addClass('has-error');

                        if ($('[name="'+i+'"]').closest('.modern-radios').length) {
                            $('[name="'+i+'"]').closest('.modern-radios').addClass('has-error');
                        }
                        
                        console.log(i);
                        if(i == 'agree') {
                            $('.agree-error').show();
                        }
                    }

                    $('html, body').animate({
                        scrollTop: $('.ajax-alert:visible').first().offset().top - $('header').height() - 150
                    }, 500);
                }
                ajax_is_running = false;
            }).bind(that), "json"
        );

    });
    
    var handleHorizontalScrolls = function() {
        var scrollableElement = $('.sample-pages .flex');
        var children = scrollableElement.children();
        var total = 0;

        children.each( function() { 
            total += $(this).outerWidth() + parseFloat($(this).css('margin-right')) + parseFloat($(this).css('margin-left'));
        });

        scrollableElement.css('width', total + parseFloat(scrollableElement.css('padding-left')) + parseFloat(scrollableElement.css('padding-right')) + 60);

    }

    if($(window).outerWidth() <= 768 && $('.sample-pages').length) {
        setTimeout(handleHorizontalScrolls , 10);
    }

});