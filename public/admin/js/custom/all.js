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
        $.ajax({
            url     : 'cms/vox/edit-field/'+id+'/'+field+'/'+( $(this).is(':checked') ? 1 : 0 ),
            type    : 'GET'
        });

	} );

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
		$('#mass-bump').click( function(e) {
			e.preventDefault();
			$(this).closest('form').attr('action', $(this).closest('form').attr('original-action')+'/'+$(this).attr('id'));
			$(this).closest('form').submit();
		});
		
		$('#mass-stop').click( function(e) {
			e.preventDefault();
			$(this).closest('form').attr('action', $(this).closest('form').attr('original-action')+'/'+$(this).attr('id'));
			$(this).closest('form').submit();
		});
	}



	$('#add-avatar').change( function() {
        if (typeof($(this)[0].files[0]) != 'undefined' ) {
            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            $('.image-label').addClass('loading');

            var file = $(this)[0].files[0];
            var upload = new Upload(file, $(this).attr('upload-url'), function(data) {
                $('.image-label').removeClass('loading');
                $('.image-label').css('background-image', "url('"+data.thumb+"')");

                if ($('.centered-hack').length) {
                    $('.centered-hack').hide();
                }

                ajax_is_running = false;
            });

            upload.doUpload();
        }

    } );




    Upload = function (file, url, success) {
        this.file = file;
        this.url = url;
        this.success = success
    };

    Upload.prototype.getType = function() {
        return this.file.type;
    };
    Upload.prototype.getSize = function() {
        return this.file.size;
    };
    Upload.prototype.getName = function() {
        return this.file.name;
    };
    Upload.prototype.doUpload = function () {
        var that = this;
        var formData = new FormData();

        // add assoc key values, this will be posts values
        formData.append("image", this.file, this.getName());
        formData.append("upload_file", true);

        $.ajax({
            type: "POST",
            url: this.url,
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', that.progressHandling, false);
                }
                return myXhr;
            },
            success: this.success,
            error: function (error) {
                ajax_is_running = false;
            },
            async: true,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });
    };

    Upload.prototype.progressHandling = function (event) {
        var percent = 0;
        var position = event.loaded || event.position;
        var total = event.total;
        var progress_bar_id = "#progress-wrp";
        if (event.lengthComputable) {
            percent = Math.ceil(position / total * 100);
        }
        console.log(percent);
    };

});

