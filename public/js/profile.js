$(document).ready(function(){

    //Avatars
	$('#add-avatar').change( function(){

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

	    var file = $(this)[0].files[0];
	    var upload = new Upload(file, $(this).closest('form').attr('action'), function (data) {
            console.log(data);
            $('#avatar-add').removeClass('loading').addClass('has-image');
            $('#avatar-add').find('img').attr('src', data.url + '?rand='+Math.random());
            ajax_is_running = false;
            // your callback here
        });

	    $(this).closest('form').removeClass('has-image').addClass('loading');

	    upload.doUpload();
	    
	});

    $('.changer').click( function() {
        $('#add-avatar').trigger('click'); 
    } )

    //Gallery
    $('.gallery-pic input').change( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        if($(this).closest('.gallery-pic').hasClass('empty')) {
            var position = 8 - $('.gallery-pic.empty').length;
        } else {
            var position = $(this).closest('.gallery-pic').attr('data-position');            
        }


        $('#gallery-photo-'+position).removeClass('empty').addClass('loading');

        var file = $(this)[0].files[0];
        var upload = new Upload(file, $(this).closest('form').attr('action') + '/' + position, function(data) {
            console.log(data);
            $('#gallery-photo-'+data.position).removeClass('loading');
            $('#gallery-photo-'+data.position).find('img').attr('src', data.url + '?rand='+Math.random());
            ajax_is_running = false;
        });

        upload.doUpload();

    } );

    $('.gallery-pic .deleter').click( function(e) {
        e.preventDefault();
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        r = confirm(areYouSure);
        if(r) {
            $.ajax( {
                url: $(this).attr('href'),
                type: 'GET',
                dataType: 'json',
                success: function( data ) {
                    ajax_is_running = false;
                    window.location.reload();
                }
            });

        }
    } );
    $('.gallery-pic .editor').click( function(e) {
        $(this).closest('.gallery-pic').find('input').click();
    });

    //Invites
    $('#invite-patient-form').submit( function(e) {
        e.preventDefault();

        $('#invite-alert').hide();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $('#invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#invite-email').val('');
                    $('#invite-name').val('').focus();
                    $('#invite-alert').show().addClass('alert-success').html(data.message);
                } else {
                    $('#invite-alert').show().addClass('alert-warning').html(data.message);                    
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;
    } );

    //Wallet
    $('#transfer-form').submit( function(e) {
        e.preventDefault();

        sendDCN( $('#transfer-wallet-address').val(), $('#transfer-wallet-amount').val() );
        
    });

    //Rewards
    
    $('#reward-form').submit( function(e) {
        e.preventDefault();

        $('.panel-body-reward .alert').hide();

        var dcn_address = $('#transfer-reward-address').val();
        if ( typeof web3 !== 'undefined' && !web3.isAddress(dcn_address) ) {
            $('#reward-invalid').show();
            return;
        }

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                if(data.success) {
                    $('#reward-succcess').show();
                    $('#reward-succcess a').attr('href', data.link);
                    $('#reward-succcess a').html(data.link);
                    $('#reward-form').remove();
                } else {
                    $('#reward-error').show();
                    if(data.message) {
                        $('#reward-reason').show().html( data.message );
                    }
                }
            },
            error: function (error) {
                $('#reward-error').show();
            }
        });
        
    });

    $('#balance-form').submit( function(e) {
        e.preventDefault();

        $('#has-no-wallet .alert').hide();

        var dcn_address = $('#transfer-balance-address').val();

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                if(data.success) {
                    $('#balance-succcess').show();
                    $('#balance-amount').html(data.result + ' DCN');
                    $('#balance-succcess a').attr('href', 'https://etherscan.io/token/0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6?a=' + dcn_address);
                } else {
                    $('#balance-error').show();
                }
            },
            error: function (error) {
                $('#balance-error').show();
            }
        });
        
    });

    //Profile info
    $('.work-hour-cb').change( function() {
        var active = $(this).is(':checked');
        console.log(active);
        var texts = $(this).closest('.form-group').find('input[type="text"]');
        if(active) {
            texts.prop("disabled", false);
        } else {
            texts.attr('disabled', 'disabled');
        }
    } )




});


var Upload = function (file, url, success) {
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
            // handle error
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