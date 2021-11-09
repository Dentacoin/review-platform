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

	$('.user-info').click( function() {
		var that = $(this);
		
    	$.ajax( {
			url: window.location.origin+'/cms/ban_appeals/info/'+that.attr('user-id'),
			type: 'POST',
			dataType: 'json',
			success: function( data ) {
				that.closest('.user-info-wrapper').find('.user-info-tooltip').html(data.data);
			},
			error: function(data) {
				console.log('error');
			}
		});
	});

	$('#check-cur-pending-tx').click( function() {		
    	$.ajax( {
			url: window.location.origin+'/cms/check-pending-trans/',
			type: 'POST',
			dataType: 'json',
			success: function( data ) {
				$('#cur-pending-tx').html(data.data);
			},
			error: function(data) {
				console.log('error');
			}
		});
	});

	$('#check-cur-nodes').click( function() {		
    	$.ajax( {
			url: window.location.origin+'/cms/check-nodes/',
			type: 'POST',
			dataType: 'json',
			success: function( data ) {
				$('#cur-nodes').html(data.data);
			},
			error: function(data) {
				console.log('error');
			}
		});
	});

	$('#server_pending_trans_check, #connected_nodes_check').change( function() {

		var checked = $(this).is(':checked');
		var is_pending = $(this).attr('id') == 'server_pending_trans_check' ? true : false;
		if(checked) {
			if(!confirm('Are you sure you want to '+(is_pending ? 'check for server pending transactions' : 'check for connected nodes')+'?')){         
				$(this).removeAttr('checked');
			} else {
				if(is_pending) {
					$('#count_pending_transactions').removeAttr("disabled");
				}
			}
		} else {
			if(!confirm('Are you sure you DON\'T want to '+(is_pending ? 'check for server pending transactions' : 'check for connected nodes')+'?')){
				$(this).attr("checked", "checked");
			} else {
				if(is_pending) {
					$('#count_pending_transactions').prop('disabled', 'disabled');
				}
			}
		}
	});

	///on load page
	if($('#server_pending_trans_check').is(':checked')) {
		$('#count_pending_transactions').removeAttr("disabled");
	} else {
		$('#count_pending_transactions').prop('disabled', 'disabled');
	}
	
});