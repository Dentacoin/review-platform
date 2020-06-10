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

	$('#mass-delete-button').click( function(e) {
		e.preventDefault();

		$('#massDeleteModal').modal('show');
	});

	$('#massDeleteModalButton').click( function(e) {
		e.preventDefault();

		$('#mass-delete-form').find('input[name="mass-delete-reasons"]').val($(this).parent().find('textarea').val());
		$('#mass-delete-form').submit();
	});
    
    $('.external-patients-button').click( function() {
        $(this).closest('.external-patients-wrap').next().show();
        $(this).closest('.external-patients-wrap').hide();
    });

    $('.btn-add-new-badge').click( function() {
		var newinput = $('#badge-group-template').clone(true).removeAttr('id');
		$('.top-list').append(newinput);
	} );

	$('.btn-remove-badge').click( function() {
		$(this).closest('.input-group').remove();
	} );


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

});