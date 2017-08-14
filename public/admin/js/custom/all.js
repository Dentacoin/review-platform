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

	$('.table-filters').each( function (e) {
		if($(this).find('.form-group:visible').length) {
			$(this).find('legend').show();
		}
	});

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
			    for(var i in data) {
    				city_select.append('<option value="'+i+'">'+data[i]+'</option>');
			    }
				//city_select
				//$('#modal-message .modal-body').html(data);
			}
		});
    	$.ajax

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

});

