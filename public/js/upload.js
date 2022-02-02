var Upload;
var uploadTeamImage;
var croppie_instance;

$(document).ready(function(){
    
	$('#add-avatar, .add-avatar-member, #add-avatar-patient, .add-avatar-clinic-branch').change( function() {
        if (typeof($(this)[0].files[0]) != 'undefined' ) {
            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            $(this).closest('.image-label').addClass('loading');

            var file = $(this)[0].files[0];
            var that = $(this);
            var main_parent = that.closest('.upload-image-wrapper');

            var fileExtension = ['jpeg', 'jpg', 'png'];
            if ($.inArray(that.val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                console.log("Only formats are allowed : "+fileExtension.join(', '));
            } else {

                if(file.size > 2000000) {
                    main_parent.find('.image-big-error').show();
                    that.closest('.image-label').removeClass('loading');
                    ajax_is_running = false;
                } else {
                    main_parent.find('.image-big-error').hide();
                    var upload = new Upload(file, $(this).attr('upload-url'), function(data) {
        
                        that.closest('.image-label').removeClass('loading');
        
                        if(!that.hasClass('no-cropper')) {
                            main_parent.find('.avatar-name').text(file.name);
                            main_parent.find('.avatar-name-wrapper').show();
            
                            that.closest('.image-label').hide();
                            main_parent.find('.cropper-container').show();
        
                            var croppieParams = {
                                enableOrientation: true,
                                enforceBoundary: false
                            };
                    
                            croppieParams.viewport = {
                                width: 180,
                                height: 180
                            };
                            croppieParams.boundary = {width: 180, height: 180};
                    
                            croppie_instance = main_parent.find('.cropper-container').croppie(croppieParams);
        
                            croppie_instance.croppie('bind', {
                                url: data.thumb
                            });
                            
                            main_parent.find('.cropper-container').on('update.croppie', function(ev, cropData) {
                                croppie_instance.croppie('result', {
                                    type: 'canvas',
                                    size: {width: 180, height: 180},
                                }).then(function (src) {
                                    main_parent.find('.avatar').val(src);
                                });
                            });
        
                        } else {
        
                            that.closest('.image-label').removeClass('loading');
                            that.closest('.image-label').css('background-image', "url('"+data.thumb+"')");
        
                            if(that.closest('.image-label').find('.centered-hack').length) {
                                that.closest('.image-label').find('.centered-hack').remove();
                            }
        
                            if(that.attr('id') == 'add-avatar') {
                                if( $('#photo-name').length ) {
                                    $('#photo-name').val( data.name );
                                }
                                if( $('#photo-thumb').length ) {
                                    $('#photo-thumb').val( data.thumb );
                                }
                            } else if(that.hasClass('add-avatar-member')) {
                                if(that.parent().parent().find('.photo-name-team').length) {
                                    that.parent().parent().find('.photo-name-team').val( data.name );
                                }
                                if( that.parent().parent().find('.photo-thumb-team').length ) {
                                    that.parent().parent().find('.photo-thumb-team').val( data.thumb );
                                }
                            } else if(that.attr('id') == 'add-avatar-patient') {
                                if(that.parent().parent().find('.photo-name').length) {
                                    that.parent().parent().find('.photo-name').val( data.name );
                                }
                                if( that.parent().parent().find('.photo-thumb').length ) {
                                    that.parent().parent().find('.photo-thumb').val( data.thumb );
                                }
                            } else if(that.hasClass('add-avatar-clinic-branch')) {
                                if(that.parent().parent().find('.photo-name-branch').length) {
                                    that.parent().parent().find('.photo-name-branch').val( data.name );
                                }
                                if( that.parent().parent().find('.photo-thumb-branch').length ) {
                                    that.parent().parent().find('.photo-thumb-branch').val( data.thumb );
                                }
                            }
                        }
        
                        ajax_is_running = false;
                    });
        
                    upload.doUpload();
                }
            }
        }
    });

    $('.destroy-croppie').click( function() {
        if (croppie_instance != undefined) {
			croppie_instance.croppie('destroy');

            var main_parent = $(this).closest('.upload-image-wrapper');
            main_parent.find('.avatar-name-wrapper').hide();

			$('#cropper-container').html('');
            main_parent.find('.cropper-container').hide();
            main_parent.find('.image-label').show();
		}
    });

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