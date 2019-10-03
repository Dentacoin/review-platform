var Upload;
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