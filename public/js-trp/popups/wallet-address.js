$(document).ready(function() {

    $('#recieve-address').change( function() {
        if($(this).is(':checked')) {
            $('.receive-wallet-address-wrapper').show();
            $('.submit-wallet-address span').html($('.submit-wallet-address').attr('multiple-address'));
        } else {
            $('.receive-wallet-address-wrapper').hide();
            $('.submit-wallet-address span').html($('.submit-wallet-address').attr('single-address'));
        }
    });
    
    $('.wallet-address-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $(this).find('.ajax-alert').remove();
        $(this).find('.has-error').removeClass('has-error');
        var that = $(this);

        $('.ajax-alert').remove();
        $(this).find('.submit-wallet-address').addClass('waiting');
        
        $.post( 
            $(this).attr('action'), 
            $(this).serialize(), 
            (function( data ) {
                that.find('.submit-wallet-address').removeClass('waiting');
                console.log(data);
                if(data.success) {
                	$('#add-wallet-address').removeClass('active');
                } else {
                    for(var i in data.messages) {
                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>');
                        $('[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );
    });
});