var ajax_is_running = false;

$(document).ready(function(){
	if($('#user-save').length) {
		var country = $('#countries option:selected' ).text();
		$("#states").children("optgroup[label!='" + country + "']").hide();

		var state = $('#states option:selected' ).text();
		$("#cities").children("optgroup[label!='" + state + "']").hide();

		var city = $('#cities option:selected' ).text();
		$("#districts").children("optgroup[label!='" + city + "']").hide();

		$('#countries').change(function() {
			var country = $('#countries option:selected' ).text();
			$("#states").children("optgroup").hide();
			$("#states").children("optgroup[label='" + country + "']").show();
			$("#states").val( $("#states").children("optgroup[label='" + country + "']").children('option').first().val() );

			$("#states").change();
			$("#cities").change();
		});

		$('#states').change(function() {
			var state = $('#states option:selected' ).text();
			$("#cities").children("optgroup").hide();
			$("#cities").children("optgroup[label='" + state + "']").show();
			$("#cities").val( $("#cities").children("optgroup[label='" + state + "']").children('option').first().val() );

			$("#cities").change();
		});

		$('#cities').change(function() {
			var city = $('#cities option:selected' ).text();
			$("#districts").children("optgroup").hide();
			$("#districts").children("optgroup[label='" + city + "']").show();
			if( $("#districts").children("optgroup[label='" + city + "']").children('option').length ) {
				$("#districts").val( $("#districts").children("optgroup[label='" + city + "']").children('option').first().val() );
			} else {
				$("#districts").val('');
			}
		});
	}

	if($('#user-petsitter').length) {
		$('.service-checkbox').each( function() {
			var id = $(this).attr('id')+'-price';
			$('#'+id).prop( "disabled", !$(this).is(':checked') );			
		});

		$('.service-checkbox').change( function() {
			var id = $(this).attr('id')+'-price';
			$('#'+id).prop( "disabled", !$(this).is(':checked') );			
		});
	}

	$('.user-messages-load-more').click( function() {
		$('#modal-message .modal-body').html('Loading');
		$.ajax( {
			url: $(this).attr('data-ajax-href'),
			type: 'GET',
			success: function( data ) {
				console.log(data);
				$('#modal-message .modal-body').html(data);
			}
		});
	});

	$('#edit-slug').click( function(e) {
		e.preventDefault();
		$('#user-slug').prop("disabled", false);
	});


	$('.deletion-button').click( function() {
		var action = $('#deleteModal form').attr('original-action') + '/' + $(this).attr('user-id');
		$('#deleteModal form').attr('action' , action);
	});

	$('#massDeleteModalButton').click( function(e) {
		e.preventDefault();

		$('#mass-delete-form').find('input[name="mass-delete-reasons"]').val($(this).parent().find('textarea').val());
		$('#mass-delete-form').attr('action', $('#mass-delete-form').attr('mass-delete-action'));
		$('#mass-delete-form').submit();
	});

	$('#mass-delete-button').click( function(e) {
		e.preventDefault();

		$('#massDeleteModal').modal('show');
	});

	$('#mass-reject-button').click( function(e) {
		e.preventDefault();

		$('#mass-delete-form').attr('action', $('#mass-delete-form').attr('mass-reject-action'));
		$('#mass-delete-form').submit();
	});

	$('#mass-approve-button').click( function(e) {
		e.preventDefault();

		$('#mass-delete-form').attr('action', $('#mass-delete-form').attr('mass-approve-action'));
		$('#mass-delete-form').submit();
	});
    
    $('.external-patients-button').click( function() {
        $(this).closest('.external-patients-wrap').next().show();
        $(this).closest('.external-patients-wrap').hide();
    });

    $('.btn-add-new-badge').click( function() {
		var newinput = $('#badge-group-template').clone(true).removeAttr('id');
		$('.top-list').append(newinput);
	});

    $('.btn-add-new-year-badge').click( function() {
		var newinput = $('#badge-group-year-template').clone(true).removeAttr('id');
		$('.top-list-year').append(newinput);
	});

	$('.btn-remove-badge, .btn-remove-year-badge').click( function() {
		$(this).closest('.input-group').remove();
	});


	var userFilter = function() {
		if($('#search-platform').val() != '') {
			$('.users-filters .filter').hide();
			if($('#search-platform').val() == 'vox') {
				$('.users-filters .vox-filter').show();
			} else {
				$('.users-filters .trp-filter').show();
			}
		} else {
			$('.users-filters .filter').hide();
		}
	}

	userFilter();

	$('#search-platform').change( function() {
		userFilter();
	});

	if ($('.select2').length) {
        $(".select2").select2({
            multiple: true,
            placeholder: 'Exclude Country/ies',
        });
    }

	if ($('.select2type').length) {
		$(".select2type").multiSelect();

		if( $('.multi-select-button').text() == '-- Select --') {

			$('.multi-select-button').html('User types')
		}
    }

    $('#custom_lat_lon').change( function() {

    	if ($(this).is(':checked')) {
			$('#lat').removeAttr('disabled');
			$('#lon').removeAttr('disabled');
    	} else {
    		$('#lat').prop('disabled', true);
    		$('#lon').prop('disabled', true);
    	}
    });

    $('.preferences-button').click( function() {
    	var email = $(this).attr('email');

    	$.ajax({
            type: 'POST',
            url: 'https://api.dentacoin.com/api/anonymous-email-preferences',
            data: {
                email: email
            },
            dataType: 'json',
            success: function (response) {
            	if(response.data) {
            		for( var i in response.data) {
            			if(i == 'product_news' || i == 'blog') {

            				for(var u in response.data[i]) {
            					var value = response.data[i][u]['checked'] ? '<i class="fa fa-check-square-o" aria-hidden="true"></i>' : '<i class="fa fa-square-o" aria-hidden="true"></i>';
            					$('#'+i).find('.'+u).html(value);
            				}
            			}
            		}
            	}
                console.log(response);
            }
        });
    });


    $('[name="patient_status"]').change( function() {
    	if($(this).val() == 'suspicious_admin') {
    		$('#suspicious-reason').show();
    	} else {
    		$('#suspicious-reason').hide();
    	}
    });

    $('#check-kyc').click( function() {
    	var token = $(this).attr('civic-token');

    	$.ajax({
            type: 'POST',
            url: 'https://api.dentacoin.com/api/test-civic',
            data: {
                jwtToken: token
            },
            dataType: 'json',
            success: function (response) {
            	if(response.res.success) {
            		$('#kyc-result').html('success');
            		// for( var i in response.data) {
            		// 	if(i == 'product_news' || i == 'blog') {

            		// 		for(var u in response.data[i]) {
            		// 			var value = response.data[i][u]['checked'] ? '<i class="fa fa-check-square-o" aria-hidden="true"></i>' : '<i class="fa fa-square-o" aria-hidden="true"></i>';
            		// 			$('#'+i).find('.'+u).html(value);
            		// 		}
            		// 	}
            		// }
            	} else {
            		$('#kyc-result').html(response.res.errors.token);
            		 //token
            	}
                console.log(response);
            }
        });
    });

    $('.show-history').click( function(e) {
    	e.preventDefault();

    	$(this).next().show();
    	$(this).hide();
    });

	$('.scroll-to').click( function(e) {
		e.preventDefault();

		var item = $(this).attr('href');
		$('body, html').animate({
            scrollTop: $(item).offset().top - 50
        }, 500);
	});

	$('[name="vip_access"]').change( function() {
		if($(this).is(':checked')) {
			$('[name="vip_access_until"]').closest('div').show();
			$('[name="vip_access_until"]').closest('div').prev().show();
		} else {
			$('[name="vip_access_until"]').closest('div').hide();
			$('[name="vip_access_until"]').closest('div').prev().hide();
		}
	});

	$('#answered-questions-count-form').submit( function(e) {
        e.preventDefault();

        var formData = new FormData(this);
		var that = $(this);
		that.find('p').css('display', 'block');

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
				that.parent().find('table tbody').append('<tr>\
					<td>'+that.attr('date')+'</td>\
					<td>'+data.data+'</td>\
				</tr>');
				that.find('p').hide();
			}

	    }).bind(this) ).fail(function (data) {
			console.log(data);
	    });
    });
});