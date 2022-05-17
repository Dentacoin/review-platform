$(document).ready(function(){

    $('.next-branch-button').click( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);
        var url = $(this).attr('branch-url');

        $('.ajax-alert').remove();

        $.post( 
            $(this).attr('branch-url'), 
            $('.add-new-branch-form').serialize(), 
            function( data ) {
                console.log(data);
                if(data.success) {
                    $('.branch-content').hide();
                    $('.branch-tabs .step').removeClass('active');
                    $('.branch-tabs .step[data-branch="'+that.attr('to-step')+'"]').addClass('active');
                    $('#branch-option-'+that.attr('to-step')).show();

                    $('.clinic_address.address-suggester-input').removeAttr('placeholder');
                } else {
                    for(var i in data.messages) {
                        $('[name="'+i+'"]').addClass('has-error');
                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');
                    }
                }
                ajax_is_running = false;
            }, 
            "json"
        );
    });
    
    $('.add-new-branch-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $('.ajax-alert').remove();
        
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                console.log(data);
                if(data.success) {
                	if($('body').hasClass('page-branches')) {
                		window.location.reload();
                	} else {
                		window.location.href = $(this).attr('success-url');
                	}
                } else {
                    $('.last-step-flex').after('<div class="alert alert-warning ajax-alert"></div>');
                    for(var i in data.messages) {
                        $('.add-new-branch-form .ajax-alert').append(data.messages[i] + '<br/>');

                        $('[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );          
    });

    $('.prev-branch-button').click( function() {
        console.log('click');
        $('.branch-content').hide();
        $('.branch-tabs .step').removeClass('active');
        $('.branch-tabs .step[data-branch="'+$(this).attr('to-step')+'"]').addClass('active');
        $('#branch-option-'+$(this).attr('to-step')).show();

        if ($('#clinic_address').length && $('#clinic_address').val()) {
            $('#clinic_address').blur();
        }
    });

});