$(document).ready(function(){

    var balanceUpdater = function() {
        $.post( 
            $('#balance-address').val(), 
            $('#balance-form').serialize() , 
            function( data ) {
                if(data.success) {
                    $('#my-balance').val(data.result);
                } else {
                }
            }, "json"
        );
    }

    if($('#balance-address').length) {
        balanceUpdater();
    }


    $('#withdraw-form').submit( function(e) {
        e.preventDefault();
        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;
        $('#withdraw-form .alert').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#withdraw-success').show();
                    $('#withdraw-success a').attr('href', data.link);
                    $('#withdraw-success a').html(data.link);
                    //balanceUpdater();
                    if(data.balance) {
                        $('#menu-balance').html(data.balance + ' DCN');
                        $('#header-balance').html(data.balance + ' DCN');
                        
                    }
                } else {
                    $('#withdraw-error').show();
                    if(data.message) {
                        $('#withdraw-reason').show().html( data.message );
                    }
                }
                ajax_is_running = false;
            }, "json"
        );

    } );



});