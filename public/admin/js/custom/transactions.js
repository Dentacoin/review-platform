$(document).ready(function(){

	// var lm_handler = function() {
	// 	$('#modal-message-transactions .modal-body').html('Loading');
	// 	$.ajax( {
	// 		url: $(this).attr('data-ajax-href'),
	// 		type: 'GET',
	// 		success: set_transaction_handlers
	// 	});
	// }

	// var transations_edit_handler = function(e) {
	// 	e.preventDefault();
 //        var action = $("input[type=submit][clicked=true]").attr('id');
	// 	$.ajax( {
	// 		url: $(this).attr('action'),
	// 		type: 'POST',
	// 		data: {
	// 			'action': action.replace('transaction-edit-', ''),
	// 			'custom_amount': $('#custom_amount').val(),
	// 			'description': $('#transaction-description').val(),
	// 			'payment_id': $('#payment_id').val(),
	// 			'_token' : $('#modal-message-transactions #transaction-edit input[name="_token"]').val()
	// 		},
	// 		success: set_transaction_handlers
	// 	});
	// }

	// var set_transaction_handlers = function( data ) {
	// 	var arr = data.split('|');
		
	// 	$('#modal-message-transactions .modal-title').html(arr[0]);
	// 	$('#modal-message-transactions .modal-body').html(arr[1]);
	// 	$('#modal-message-transactions .transaction-load-more').click(lm_handler);
	// 	$('#modal-message-transactions #transaction-edit').submit( transations_edit_handler );
	//     $("#modal-message-transactions #transaction-edit input[type=submit]").click(function() {
	//         $("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
	//         $(this).attr("clicked", "true");
	//     });

	// }

	// $('.transaction-load-more').click( lm_handler );
});