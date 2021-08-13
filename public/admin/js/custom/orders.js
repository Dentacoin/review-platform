$(document).ready(function(){

	$('.add-payment-info').click( function() {
		var action = $('#paymentModal form').attr('original-action') + '/' + $(this).attr('order-id');
		$('#paymentModal form').attr('action' , action);
		$('#paymentModal form').attr('order-id' , $(this).attr('order-id'));
        $('#paymentModal [name="payment-info"]').val($(this).attr('payment-info'));
	});

	$('.payment-info-form').submit( function(e) {
        e.preventDefault();

        var formData = new FormData(this);
		var order_id = $(this).attr('order-id');

        $.ajax({
	        url: $(this).attr('action'),
	        type: 'POST',
	        data: formData,
	        cache: false,
	        contentType: false,
	        processData: false
	    }).done( (function (data) {
			console.log(data);

			if(data.success) {
				$('.modal').modal('hide');
				$('td.order-'+order_id).html(data.payment_info);
                $('.add-payment-info[order-id="'+order_id+'"]').attr('payment-info', data.payment_info);
			}

	    }).bind(this) ).fail(function (data) {
			console.log(data);
	    });

    } );
});

