
$(document).ready(function() {

    $('.recommend-dentist-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $(this).find('.recommend-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        var formData = new FormData(this);

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            success: function (data) {
                if(data.success) {
                    $('.recommend-dentist-form').find('.recommend-email').val('');
                    $('.recommend-dentist-form').find('.recommend-name').val('').focus();
                    $('.recommend-dentist-form').find('.recommend-alert').show().addClass('alert-success').html(data.message);

                    gtag('event', 'Submit', {
                        'event_category': 'Recommend',
                        'event_label': 'RecommendSent',
                    });
                } else {
                    $('.recommend-dentist-form').find('.recommend-alert').show().addClass('alert-warning').html(data.message);                    
                }
                ajax_is_running = false;
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
    });

    $('.recommend-button').click( function() {
        gtag('event', 'Open', {
            'event_category': 'Recommend',
            'event_label': 'RecommendPopup',
        });
    });
});