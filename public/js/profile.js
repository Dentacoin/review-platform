var dentistTO = null;
var user_id;
$(document).ready(function(){


    


    //Profile info
    $('.work-hour-cb').change( function() {
        var active = $(this).is(':checked');
        console.log(active);
        var texts = $(this).closest('.form-group').find('input[type="text"]');
        if(active) {
            texts.prop("disabled", false);
        } else {
            texts.attr('disabled', 'disabled');
        }
    } )

    //Widget

    $('.btn-group-justified label').click( function() {
        var id = $(this).attr('for');
        $('.option-div').hide();
        $('#option-mode').show();
        $('#widget-preview').show();
        $('#'+id).show();
        $('.btn-group-justified .btn').removeClass('btn-primary');
        $(this).addClass('btn-primary');
    } );

    var refreshWidgetCode = function() {
        if(typeof widet_url=='undefined') {
            return
        }
        var wmode = parseInt($('.widget-modes input:checked').val());
        wmode = isNaN(wmode) ? 0 : wmode;
        var parsedUrl = widet_url.replace('{mode}', wmode);
        $('#option-iframe textarea').val('<iframe style="width: 100%; height: 50vh; border: none; outline: none;" src="'+parsedUrl+'"></iframe>');
        $('#option-js textarea').val('<div id="trp-widget"></div><script type="text/javascript" src="https://reviews.dentacoin.com/js/widget.js"></script> <script type="text/javascript"> TRPWidget.init("'+parsedUrl+'"); </script>');
    }

    $('.widget-modes input').change(refreshWidgetCode);
    refreshWidgetCode();

    var dentistSuggester = function() {
        if( $(this).val().trim().length < 4 ) {
            return;
        }

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $(this).closest('.dentist-suggester').addClass('loading');

        var that = $(this);

        $.ajax( {
            url: 'suggest-dentist/'+user_id,
            type: 'POST',
            dataType: 'json',
            data: {
                invitedentist: $(this).val()
            },
            success: (function( data ) {
                console.log(data);
                that.closest('.dentist-suggester').removeClass('loading').addClass('visible');
                var container = that.closest('.dentist-suggester').find('.results').first();

                if (data.length) {
                    container.html('');
                    for(var i in data) {
                        container.append('<a href="javascript:;" data-id="'+data[i].id+'">'+data[i].name+'</a>');
                    }

                    container.find('a').click( function() {
                        $('#invitedentistid').val( $(this).attr('data-id') );
                        $(this).closest('.dentist-suggester').removeClass('visible');
                        $(this).closest('form').find('#invitedentist').val( $(this).html() );
                    } );
                } else {
                    container.html('No dentist found by that name');
                }
                ajax_is_running = false;
            }).bind(that)
        });
    };

    $('#invitedentist').keydown( function(event) {

        if( event.keyCode == 13) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        }

        if(dentistTO) {
            clearTimeout(dentistTO);
        }

        dentistTO = setTimeout(dentistSuggester.bind(this), 300);
    } );


});

