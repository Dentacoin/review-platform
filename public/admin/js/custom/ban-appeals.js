var ajax_is_running = false;

$(document).ready(function(){

	$('.reject-appeal').click( function() {
		var action = $('#rejectedModal form').attr('original-action') + '/' + $(this).attr('appeal-id');
		$('#rejectedModal form').attr('action' , action);
	});

	$('.approve-appeal').click( function() {
		var action = $('#approvedModal form').attr('original-action') + '/' + $(this).attr('appeal-id');
		$('#approvedModal form').attr('action' , action);
	});

	$('.pending-appeal').click( function() {
		console.log('dsfdsf');
		var action = $('#pendingModal form').attr('original-action') + '/' + $(this).attr('appeal-id');
		$('#pendingModal form').attr('action' , action);
	});

	$('.ban-appeal-info').click( function() {
		var that = $(this);
		
    	$.ajax( {
			url: window.location.origin+'/cms/ban_appeals/info/'+that.attr('user-id'),
			type: 'POST',
			dataType: 'json',
			success: function( data ) {
				that.closest('.ban-appeal-wrapper').find('.ban-appeal-tooltip').html(data.data);
			},
			error: function(data) {
				console.log('error');
			}
		});
	});

});