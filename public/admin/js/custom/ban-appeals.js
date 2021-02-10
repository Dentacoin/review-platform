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

});