$(document).ready(function(){

	$('.emails-load-more').click( function() {
		$('#modal-message .modal-body').html('Loading');
		$.ajax( {
			url: $(this).attr('data-ajax-href'),
			type: 'GET',
			success: function( data ) {
				var arr = data.split('|');
				
				$('#modal-message .modal-title').html(arr[0]);
				$('#modal-message .modal-body').html(arr[1]);
			}
		});
	});

});