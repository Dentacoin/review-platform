$(document).ready(function(){

    $('.next-branch-button').click( function() {
        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);
        var url = $(this).attr('branch-url');

        $('.ajax-alert').remove();

        $.post( 
            $(this).attr('branch-url'), 
            $('.add-new-branch-form').serialize(), 
            function( data ) {
                console.log(data);
                if(data.success) {
                    $('.branch-content').hide();
                    $('.branch-tabs a').removeClass('active');
                    $('.branch-tabs a[data-branch="'+that.attr('to-step')+'"]').addClass('active');
                    $('#branch-option-'+that.attr('to-step')).show();

                    $('.clinic_address.address-suggester-input').removeAttr('placeholder');
                } else {
                    for(var i in data.messages) {
                        $('[name="'+i+'"]').addClass('has-error');
                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert">'+data.messages[i]+'</div>');
                    }
                }
                ajax_is_running = false;
            }, 
            "json"
        );
    });
    
    $('.add-new-branch-form').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        $('.ajax-alert').remove();
        
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            (function( data ) {
                console.log(data);
                if(data.success) {
                	if($('body').hasClass('page-branches')) {
                		window.location.reload();
                	} else {
                		window.location.href = $(this).attr('success-url');
                	}
                } else {
                    $('.last-step-flex').after('<div class="alert alert-warning ajax-alert"></div>');
                    for(var i in data.messages) {
                        $('.add-new-branch-form .ajax-alert').append(data.messages[i] + '<br/>');

                        $('[name="'+i+'"]').addClass('has-error');
                    }
                }
                ajax_is_running = false;
            }).bind(this), "json"
        );          
    });

    $('.prev-branch-button').click( function() {
        console.log('click');
        $('.branch-content').hide();
        $('.branch-tabs a').removeClass('active');
        $('.branch-tabs a[data-branch="'+$(this).attr('to-step')+'"]').addClass('active');
        $('#branch-option-'+$(this).attr('to-step')).show();

        if ($('#clinic_address').length && $('#clinic_address').val()) {
            $('#clinic_address').blur();
        }
    });

    $('.login-as').click( function(e) {

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);

        if(!that.hasClass('result-container')) {
            that.html('<i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i>');
        }

        // $.ajax({
        //     type: "GET",
        //     url: $(this).attr('logout-url'),
        //     success: function(data) {
        //         if(data.success) {
        //             $('.sso img').remove();

        //             for( var i in data.imgs_urls) {
        //                 $('body').append('<img class="sso-imgs hide" src="'+data.imgs_urls[i]+'"/>');
        //             }

        //             var ssoTotal = $('.sso-imgs').length;
        //             var ssoLoaded = 0;
        //             $('.sso-imgs').each( function() {
        //                 if( $(this)[0].complete ) {
        //                     ssoLoaded++;        
        //                     if(ssoLoaded==ssoTotal) {
                                $.ajax({
                                    type: "POST",
                                    url: that.attr('login-url'),
                                    data: {
                                        branch_id: that.attr('branch-id'),
                                        _token: $('input[name="_token"]').val(),
                                    },
                                    success: function(ret) {
                                        // $('.sso img').remove();

                                        // for( var i in ret.imgs_urls) {
                                        //     $('body').append('<img class="sso-imgs hide" src="'+ret.imgs_urls[i]+'"/>');
                                        // }

                                        // var ssoTotal = $('.sso-imgs').length;
                                        // var ssoLoaded = 0;
                                        // $('.sso-imgs').each( function() {
                                        //     if( $(this)[0].complete ) {
                                        //         ssoLoaded++;        
                                        //         if(ssoLoaded==ssoTotal) {
                                        //             window.location.href = window.location.origin;
                                        //         }
                                        //     }
                                        // });

                                        // var ssoLoaded = 0;
                                        // $('.sso-imgs').on('load error', function() {
                                        //     ssoLoaded++;        
                                        //     if(ssoLoaded==ssoTotal) {
                                            if(typeof that.attr('redirect-url') !== 'undefined' && that.attr('redirect-url') !== false) {
                                                window.location.href = that.attr('redirect-url');
                                            } else {
                                                window.location.href = window.location.origin;
                                            }
                                            // }
                                        // });
                                    },
                                    error: function(ret) {
                                        console.log('error');
                                    }
                                });
                    //         }
                    //     }
                    // } );
                    // var ssoLoaded = 0;
                    // $('.sso-imgs').on('load error', function() {
                    //     ssoLoaded++;        
                    //     if(ssoLoaded==ssoTotal) {
                    //         $.ajax({
                    //             type: "POST",
                    //             data: {
                    //                 token: that.attr('user-token'),
                    //                 _token: $('input[name="_token"]').val(),
                    //             },
                    //             dataType: 'json',
                    //             url: that.attr('login-url'),
                    //             success: function(ret) {
                    //                 if(ret.success) {
                    //                     $('.sso-imgs').remove();

                    //                     for( var i in ret.imgs_urls) {
                    //                         $('body').append('<img class="sso-imgs hide" src="'+ret.imgs_urls[i]+'"/>');
                    //                     }  

                    //                     var ssoTotal = $('.sso-imgs').length;
                    //                     var ssoLoaded = 0;
                    //                     $('.sso-imgs').each( function() {
                    //                         if( $(this)[0].complete ) {
                    //                             ssoLoaded++;        
                    //                             if(ssoLoaded==ssoTotal) {
                    //                                 window.location.href = window.location.origin;
                    //                             }
                    //                         }
                    //                     });

                    //                     var ssoLoaded = 0;
                    //                     $('.sso-imgs').on('load error', function() {
                    //                         ssoLoaded++;        
                    //                         if(ssoLoaded==ssoTotal) {
                    //                             window.location.href = window.location.origin;
                    //                         }
                    //                     });
                    //                 }
                    //             },
                    //             error: function(ret) {
                    //                 console.log('error');
                    //             }
                    //         });
                    //     }
                    // });
        //         }
        //     },
        //     error: function(data) {
        //         console.log('error');
        //     }
        // });

        ajax_is_running = false;
    });

    $('.delete-branch').click( function(e) {

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;

        var that = $(this);

        $.ajax({
            type: "POST",
            url: that.attr('delete-url'),
            data: {
                branch_id: that.attr('branch-id'),
                _token: $('input[name="_token"]').val(),
            },
            success: function(ret) {
                that.closest('.result-container.branch').remove();
            },
            error: function(ret) {
                console.log('error');
            }
        });


        ajax_is_running = false;

    });

});