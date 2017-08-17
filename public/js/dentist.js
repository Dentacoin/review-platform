$(document).ready(function(){
	$('#write-review-btn').click( function(e) {
		e.preventDefault();

		if($(this).hasClass('verify-phone')) {
			$('#phone-verify-modal').modal();
        } else if(typeof(account)=='undefined' || !account) {
            $('#no-wallet-modal').modal();
		} else {
			$('#review-form').show();
			$(this).closest('.panel-body').remove();
		}

	} );

    $('#show-whole-review').click( function() {
        var newT = $(this).attr('data-alt-text').trim();
        $(this).attr( 'data-alt-text', $(this).html().trim() )
        $(this).html(newT);
        $(this).parent().prev().toggle();
    } );

    
    $('.show-entire-aggregation').click(function() {
        $(this).closest('.rating-panel').find('.aggregation').show();
        $(this).closest('.rating').hide();
    });
    
    $('.hide-entire-aggregation').click(function() {
        $(this).closest('.rating-panel').find('.aggregation').hide();
        $(this).closest('.rating-panel').find('.show-entire-aggregation').closest('.rating').show();
    });

	$('.label-trusted').click( function(e) {
		e.preventDefault();
		$('#trusted-modal').modal();
	});

	$('.btn-claim').click( function(e) {
		e.preventDefault();
		$('#claim-modal').modal();
	});

	$('.claim-type').change( function(){
		$(this).closest('.modal-body').find('.type-div').hide();
	    $(this).closest('.modal-body').find('.type-' + $(this).attr('data-type') ).show();
	    $(this).closest('.btn-group').find('.btn').removeClass('btn-primary').addClass('btn-default');
	    $(this).closest('.btn').addClass('btn-primary').removeClass('btn-default');
	    $(this).closest('.btn').blur();
	});


	$('.useful').click( function() {
		if($(this).hasClass('upvote-done')) {
			;
		} else if($(this).hasClass('needs-login')) {
			$(this).closest('.panel').find('.user-login-upvote').show();
        } else if($(this).hasClass('verify-phone')) {
            $('#phone-verify-modal').modal();
		//} else if(typeof(account)=='undefined' || !account) {
        //    $('#no-wallet-modal').modal();
		} else {
			var that = this;
			$.ajax( {
				url: '/'+lang+'/useful/' + $(this).attr('data-review-id'),
				type: 'GET',
				dataType: 'json',
				success: (function( data ) {
                    if(data.limit) {
                        ;
                    } else {
                        $(that).addClass('upvote-done').removeClass('btn-primary').addClass('btn-success').html( $(that).attr('data-done-text') );
    					var oc = parseInt($(that).closest('.media').find('.upvote-count').html());
    					if(oc) {
    						$(that).closest('.media').find('.upvote-count').html( ++oc );
    						$(that).closest('.media').find('.upvote-wrpapper').show();
    					}
                    }
				}).bind(that)
			});			
		}
	} );

	$('.reply-form').submit( function(e) {
		e.preventDefault();

		$(this).find('.alert').hide();

		var input = $(this).find('.review-reply').first();

		if( !input.val().trim().length ) {
			$(this).find('.alert').show();
			$('html, body').animate({
                scrollTop: $(this).offset().top - 60
            }, 500);
			return;
		}

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

		var that = $(this);

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                if(data.success) {
                	that.closest('.rating').find('.the-reply').show();
                	that.closest('.rating').find('.reply-content').html(data.reply);
                	that.remove();
                } else {
					that.find('.alert').show();

	                $('html, body').animate({
	                	scrollTop: that.offset().top - 60
	                }, 500);
                }
                ajax_is_running = false;
            }).bind(that), "json"
        );			


        return false;

    } );

	$('#write-review-form').submit( function(e) {
		e.preventDefault();

        $('#review-crypto-error').hide();
		$('#review-answer-error').hide();
		$('#review-error').hide();

		var allgood = true;

		$(this).find('input[type="hidden"]').each( function() {
			if( !parseInt($(this).val()) && $(this).attr('name')!='_token' ) {
				allgood = false;
				console.log( $(this) );
				$(this).closest('.stars').find('.rating').show();
				$('html, body').animate({
                    scrollTop: $(this).closest('.panel-body').offset().top - 60
                }, 500);
				return false;
			}
		} );

		if(allgood) {
			if( !$('#review-answer').val().trim().length ) {
				allgood = false;
				$('#review-answer-error').show();
				$('html, body').animate({
	                scrollTop: $('#review-answer').closest('.panel-body').offset().top - 60
	            }, 500);

			}
		}

		if(ajax_is_running || !allgood) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    //Dentist's ETH address goes here
                    reviewSubmitedReward('0x635c8CF5b944415b964B0451580857FE017F42dE', data.dentist_id, data.review_text, data.submit_secret, null);
                } else {
                	$('#review-error').show();

	                $('html, body').animate({
	                	scrollTop: $('#review-answer').closest('.panel-body').offset().top - 60
	                }, 500);
                }
                ajax_is_running = false;
            }, "json"
        );			


        return false;

    } );

	$('#phone-verify-send-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
        
        $('#phone-verify-send-form').find('.alert').hide();

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#phone-verify-send-form').hide();
                    $('#phone-verify-code-form').show();
                } else {
                    if(data.reason && data.reason=='phone_taken') {
                        $('#phone-taken').show();
                    } else {
                        $('#phone-invalid').show();
                    }
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#phone-verify-code-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('.verify-phone').removeClass('verify-phone');
                    $('#phone-verify-code-form').hide();
                    $('#phone-verify-success').show();
                } else {
                    $('#phone-verify-code-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#claim-phone-send-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#claim-phone-send-form').hide();
                    $('#claim-phone-code-form').show();
                } else {
                    $('#claim-phone-send-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#claim-phone-code-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#claim-phone-code-form').hide();
                    $('#claim-phone-password-form').show();
                } else {
                    $('#claim-phone-code-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#claim-phone-password-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	window.location.href = data.url;
                } else {
                    $('#claim-phone-password-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#claim-email-send-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

		$('#claim-email-send-form').find('.alert').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#claim-email-send-form').find('.tbh-div').hide();
                    $('#claim-email-send-form').find('.alert.alert-success').show();
                } else {
                    $('#claim-email-send-form').find('.alert.alert-warning').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );


    

    $('.btn-show-review').click(function() {
    	$(this).prev().toggle();
        var newT = $(this).attr('data-alt-text').trim();
        $(this).attr( 'data-alt-text', $(this).html().trim() )
        $(this).html(newT);
        
    });

    $('#review-form .ratings').mousemove( function(e) {
    	var rate = offsetToRate(e.offsetX);
    	$(this).find('.stars .bar').css('width', (rate * 30) + ((rate-1) * 18) );
    } );
    $('#review-form .ratings').click( function(e) {
    	var rate = offsetToRate(e.offsetX);
    	$(this).find('input').val(rate);
    	$(this).closest('.ratings').find('.rating').hide();
    } );
    $('#review-form .ratings').mouseout( function(e) {
    	var rate = parseInt($(this).find('input').val());
    	if(rate) {
    		$(this).find('.stars .bar').css('width', (rate * 30) + ((rate-1) * 18) );
    	} else {
    		$(this).find('.stars .bar').css('width', 0 );
    	}
    } );

});

function offsetToRate(offset) {
	return Math.ceil( offset / 48 );
}