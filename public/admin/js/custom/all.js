var Upload;
var ajax_is_running = false;
var ajax_action = false;
var last_popup_btn = null;

$(document).ready(function(){
	$('.filter-selector').change( function(e) {
		$('#filter-div-'+$(this).val()).find('input, select').prop( "disabled", false );
		$('#filter-div-'+$(this).val()).show();
		$('#filter-div-'+$(this).val()).parent().find('legend').show();
		$(this).val('');
	});

	$('.filter-remove').click( function(e) {
		$(this).parent().parent().find('input, select').prop( "disabled", true );
		$(this).parent().parent().hide();
	});

	$('.btn-export').click( function(e) {
		var extra = window.location.href.indexOf('?')==-1 ? '?export=1' : '&export=1';
		window.open(window.location.href+extra);
	});

	$('.btn-export-lead').click( function(e) {
		var extra = window.location.href.indexOf('?')==-1 ? '?export-lead=1' : '&export-lead=1';
		window.open(window.location.href+extra);
	});

	$('.btn-export-fb').click( function(e) {
		var extra = window.location.href.indexOf('?')==-1 ? '?export-fb=1' : '&export-fb=1';
		window.open(window.location.href+extra);
	});

	$('.btn-export-sendgrid').click( function(e) {
		var extra = window.location.href.indexOf('?')==-1 ? '?export-sendgrid=1' : '&export-sendgrid=1';
		window.open(window.location.href+extra);
	});

	$('.table-filters').each( function (e) {
		if($(this).find('.form-group:visible').length) {
			$(this).find('legend').show();
		}
	});

	$('.toggler').change(function(e) {
		e.preventDefault();
		
		var id = $(this).attr('id');
		var field = $(this).attr('field');

		if(field == 'type' && !$(this).is(':checked')) {
			$('#hideSurveyModal').modal('show');
			$('#hideSurveyModal').find('form').attr('action', $('#hideSurveyModal').find('form').attr('original-action')+'/'+id);
		} else {
			$.ajax({
				url     : 'cms/vox/edit-field/'+id+'/'+field+'/'+( $(this).is(':checked') ? 1 : 0 ),
				type    : 'GET',
				success: function( data ) {
					if(data.message) {
	
						$('#modal-error').modal('show');
						$('#modal-error .modal-body p').html(data.message);
					}
				}
			});
		}
	});

	$('.table-select-all').click( function() {
		
		var active = $(this).closest('table').find('input[type="checkbox"]').first().is(':checked');
		console.log( active );
		if(active) {
			$(this).closest('table').find('input[type="checkbox"]').attr('checked', false);
		} else {
			$(this).closest('table').find('input[type="checkbox"]').attr('checked', 'checked');			
		}
	} );

	$('.open-popup').click( function() {
		last_popup_btn = $(this);

		$('#modal-message .modal-title').html( $(this).attr('data-ajax-title') );
		$.ajax( {
			url: $(this).attr('data-ajax-href'),
			type: 'GET',
			success: function( data ) {
				$('#modal-message .modal-body').html(data);
			}
		});
	} );

	$('.datetimepicker').datetimepicker({
	    format: 'YYYY-MM-DD HH:mm:ss'
	});

	$('.datepicker').datepicker({
        todayHighlight: true,
        autoclose: true,
        format: 'dd.mm.yyyy',
    });

    $('.country-select').change( function() {
    	var city_select = $(this).closest('form').find('.city-select').first();
    	city_select.attr('disabled', 'disabled');
		$.ajax( {
			url: '/cities/' + $(this).val(),
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				console.log(data);
    			city_select.attr('disabled', false)
			    .find('option')
			    .remove();
			    for(var i in data.cities) {
    				city_select.append('<option value="'+i+'">'+data.cities[i]+'</option>');
			    }
				//city_select
				//$('#modal-message .modal-body').html(data);
			}
		});
    } );


	var ck_options = {
		toolbar: [
			{ name: 'styles', items: [ 'Format' ] },
			{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
			{ name: 'document', items: [ 'Source' ] },
			{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
			'/',		
			{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
			{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
			{ name: 'insert', items: [ 'Image', 'Table' ] },
		],
		enterMode: CKEDITOR.ENTER_BR,
		"extraPlugins" : 'imagebrowser',
		"imageBrowser_listUrl" : "/cms/images/json",
	};

	CKEDITOR.disableAutoInline = true;

	/*
	$('.ckeditor').each( function() {
		if( $(this).attr('id') ) {
			console.log( $(this).attr('id') );
	    	var ck = CKEDITOR.inline( $(this).attr('id'), ck_options );
	    	ck.on( 'instanceReady', function( ev ) {
			     var editor = ev.editor;
			     editor.setReadOnly( false );
			});
		}
	} );
	*/


	$('.custom-tabs .nav li a').click( function() {
		$('.custom-tabs .nav li').removeClass('active');
		$(this).parent().addClass('active');

		$(this).closest('.custom-tabs').find('.tab-pane').removeClass('active in');
		$(this).closest('.custom-tabs').find('.tab-pane.lang-'+$(this).attr('lang')).addClass('active in');

		if($('#questions-vox').length) {
			$('#questions-vox').find('.tab-pane').removeClass('active in');
			$('#questions-vox').find('.tab-pane.lang-'+$(this).attr('lang')).addClass('active in');
		}
	});
	
	$('.with-dropdown .toggle-button').click( function() {
		$(this).closest('.with-dropdown').toggleClass('active');
	});

	$('.with-dropdown').each( function() {
		if ($(this).find('.ui-sortable').length) {
			$(this).find('.ui-sortable').sortable("destroy");
		}
	});

	$('.custom-input').change( function(e) {
		$(this).closest('label').toggleClass('active');
	});

	$('.show-hide-section').each( function() {
		if ($(this).find('.ui-sortable').length) {
			$(this).find('.ui-sortable').sortable("destroy");
		}

		if($(this).find('table tbody tr').length > 10) {
			$(this).addClass('with-arrow');
			$(this).find('table tbody tr').hide();
			$(this).find('table tbody tr').slice( 0, 10 ).show();
			$(this).find('.show-all-button').show();
			$(this).find('.total-num').show();
		} else {

			$(this).find('.show-all-button').hide();
		}
	});

	$('.show-hide-button, .show-all-button').click( function() {
		var section = $(this).closest('.with-arrow');
		if(section.length) {
			section.toggleClass('active');

			if (section.hasClass('active')) {
				section.find('table tbody tr').show();
				section.find('.show-all-button').hide();
			} else {
				section.find('table tbody tr').hide();
				section.find('table tbody tr').slice( 0, 10 ).show();
				section.find('.show-all-button').show();
			}
		}
	});


	$('.limit-buttons a').click( function() {
		$(this).closest('.with-limits').find('table tbody tr').hide();
		if ($(this).attr('limit') == 'all') {
			$(this).closest('.with-limits').find('table tbody tr').show();
		} else {
			$(this).closest('.with-limits').find('table tbody tr').slice( 0, $(this).attr('limit') ).show();
		}
		
	});

	$('.with-limits').each( function() {

		if($(this).find('table tbody tr').length > 15) {
			$('.limit-buttons').show();
			$('.limit-buttons a[limit="all"]').show();
			$('.limit-buttons a[limit="15"]').show();
		}

		if ($(this).find('table tbody tr').length > 50 ) {
			$('.limit-buttons a[limit="50"]').show();
		}

		if ($(this).find('table tbody tr').length > 100 ) {
			$('.limit-buttons a[limit="100"]').show();
		}
	});


	$('input[name="results-number2"]').on('keyup keypress', function(e) {
		
		$('#users-filter-form').find('input[name="results-number"]').val($(this).val());
		var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            $('#users-filter-form').submit();
        }
	});


	if($('#mass-bump').length) {
		$('#mass-bump, #mass-stop, #mass-pending, #mass-complete').click( function(e) {
			e.preventDefault();
			$(this).closest('form').attr('action', $(this).closest('form').attr('original-action')+'/'+$(this).attr('id'));
			$(this).closest('form').submit();
		});
	}

	$('#reg-leads').change( function() {
		$('#incomplete-regs').hide();
		$('#leads').hide();

		$('#'+$(this).val()).show();
	});

    $('.reject-appeal').click( function() {
		var action = $('#rejectedModal form').attr('original-action') + '/' + $(this).attr('appeal-id');
		$('#rejectedModal form').attr('action' , action);
		$('#rejectedModal form').attr('appeal-id' , $(this).attr('appeal-id'));
		$('#rejectedModal form textarea').val('');
	});

	$('.approve-appeal').click( function() {
		var action = $('#approvedModal form').attr('original-action') + '/' + $(this).attr('appeal-id');
		$('#approvedModal form').attr('action' , action);
		$('#approvedModal form').attr('appeal-id' , $(this).attr('appeal-id'));
		$('#approvedModal form textarea').val('');
	});

	$('.pending-appeal').click( function() {
		console.log('dsfdsf');
		var action = $('#pendingModal form').attr('original-action') + '/' + $(this).attr('appeal-id');
		$('#pendingModal form').attr('action' , action);
		$('#pendingModal form').attr('appeal-id' , $(this).attr('appeal-id'));
		$('#pendingModal form textarea').val('');
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

	$('[name="approve_radio"]').click(function() {
		if($(this).attr('id') == 'approve-other') {
			$('[name="approved_reason"]').show();
		} else {
			$('[name="approved_reason"]').hide();
		}
	});

	$('[name="reject_radio"]').click(function() {
		if($(this).attr('id') == 'reject-other' || $(this).attr('id') == 'multiple-accounts') {
			if($(this).attr('id') == 'multiple-accounts') {
				$('[name="rejected_reason"]').attr("placeholder", "Write which are the multiple accounts");
			} else {
				$('[name="rejected_reason"]').attr("placeholder", "Write the reason why you want to reject this appeal");
			}
			$('[name="rejected_reason"]').show();
		} else {
			$('[name="rejected_reason"]').hide();
		}
	});

	$('.ban-appeal-form').submit( function(e) {
        e.preventDefault();

        $(this).find('.appeal-error').hide();
        var formData = new FormData(this);

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
				if(data.type == 'approved') {
					$('tr[appeal-id="'+$(this).attr('appeal-id')+'"]').find('.actions').html('Approved');
				} else if(data.type == 'rejected') {
					$('tr[appeal-id="'+$(this).attr('appeal-id')+'"]').find('.actions').html('Rejected');
				} else if(data.type == 'pending') {
					$('tr[appeal-id="'+$(this).attr('appeal-id')+'"]').find('.actions').html('Pending');
				}
				// $(this).attr('appeal-id')
			} else {
				console.log($(this));
				$(this).find('.appeal-error').html(data.message);
				$(this).find('.appeal-error').show();
			}

	    }).bind(this) ).fail(function (data) {
			console.log(data);
	    });

    } );

	$('.admin-message-wrapper .btn').click( function() {

		$.ajax({
	        url: $(this).closest('.message').attr('action'),
	        type: 'POST',
			dataType: 'json',
	    }).done( (function (data) {
			console.log(data);
			console.log('success');

			$(this).closest('.message').remove();

			if(!$('.admin-message-wrapper .message').length) {
				$('.admin-message-wrapper').remove();
			}

	    }).bind(this) ).fail(function (data) {
			console.log(data);
	    });

	});
});

