var Upload;
var uploadTeamImage;

$(document).ready(function(){
    
    $('#add-avatar, #add-avatar-mobile').change( function() {
        if (typeof($(this)[0].files[0]) != 'undefined' ) {
            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            $(this).closest('.image-label').addClass('loading');

            var file = $(this)[0].files[0];
            var that = $(this);
            var upload = new Upload(file, $(this).attr('upload-url'), function(data) {
                that.closest('.image-label').removeClass('loading');
                that.closest('.image-label').css('background-image', "url('"+data.thumb+"')");
                if(that.closest('.image-label').find('.centered-hack').length) {
                    that.closest('.image-label').find('.centered-hack').remove();
                }
                $('#photo-name').val( data.name );
                if( $('#photo-thumb').length ) {
                    $('#photo-thumb').val( data.thumb );
                }

                if( $('header .header-info .profile-btn img').length  ) {
                    $('header .header-info .profile-btn img').attr('src', data.thumb);
                }

                ajax_is_running = false;
            });

            upload.doUpload();
        }

    } );
    
    uploadTeamImage = function () {

        $('.add-avatar-member').change( function() {
            if (typeof($(this)[0].files[0]) != 'undefined' ) {
                if(ajax_is_running) {
                    return;
                }
                ajax_is_running = true;

                $(this).closest('.image-label').addClass('loading');

                var file = $(this)[0].files[0];
                var that = $(this);
                var upload = new Upload(file, $(this).attr('upload-url'), function(data) {
                    that.closest('.image-label').removeClass('loading');
                    that.closest('.image-label').css('background-image', "url('"+data.thumb+"')");
                    if(that.closest('.image-label').find('.centered-hack').length) {
                        that.closest('.image-label').find('.centered-hack').hide();
                    }

                    that.parent().parent().find('.photo-name-team').val( data.name );
                    if( that.parent().parent().find('.photo-thumb-team').length ) {
                        that.parent().parent().find('.photo-thumb-team').val( data.thumb );
                    }

                    ajax_is_running = false;
                });

                upload.doUpload();
            }

        } );
    }

    uploadTeamImage();



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