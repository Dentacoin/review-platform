$(document).ready(function(){

	$('#idea-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;
        $('#idea-form alert').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('#idea').val('');
                	$('#idea-success').show();
                } else {
                	$('#idea-error').show();
                }
                ajax_is_running = false;
            }, "json"
        );

	} );

});