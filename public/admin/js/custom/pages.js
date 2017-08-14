$(document).ready(function(){

    $('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });

    //Single Page Edit / Add

    var ck_options = {
        toolbar: [
            { name: 'styles', items: [ 'Format' ] },
            { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
            { name: 'document', items: [ 'Source' ] },
            { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
            '/',        
            { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
            { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
            { name: 'insert', items: [ 'Table' ] },
        ],
        enterMode: CKEDITOR.ENTER_BR,
        colorButton_colors: colors.join(',')
    };

    CKEDITOR.disableAutoInline = true;
    $('.page-content-div').each( function() {
        if( $(this).attr('id') ) {
            var ck = CKEDITOR.inline( $(this).attr('id'), ck_options );
            ck.on( 'instanceReady', function( ev ) {
                 var editor = ev.editor;
                 editor.setReadOnly( false );
            });

            var pad = $(this).closest('.panel-body').find('.padding').val();
            $(this).removeClass('p-none').removeClass('p-small').removeClass('p-medium').removeClass('p-big').removeClass('p-huge').addClass('p-'+pad);
        }
    } );

    $('.colorpicker').colorpicker({format: 'rgba'});

    $('.theme-color').click( function(e) {        
        $(this).closest('div').find('input.colorpicker').colorpicker('setValue',  $(this).attr('data-color') );
    } );

    $('.padding').change( function(e) {
        var pad = $(this).val();
        $(this).closest('.panel-body').find('.page-content-div').removeClass('p-none').removeClass('p-small').removeClass('p-medium').removeClass('p-big').removeClass('p-huge').addClass('p-'+pad);
    } );

    $('.template').find('a.move-up').click( function() {
        var prev = $(this).closest('.panel').prev();
        if(prev.length) {
            prev.before($(this).closest('.panel'));
        }
    } );
    $('.template').find('a.move-down').click( function() {
        var next = $(this).closest('.panel').next();
        if(next.length) {
            next.after($(this).closest('.panel'));
        }
    } );
    $('.template').find('a.remove-block').click( function() {
        var r = confirm(confirm_sure);
        if(r) {
            $(this).closest('.panel').remove();
        }       
    } );
    $('.template .background').on('changeColor blur', function() {
        $(this).closest('.panel-body').find('.ck-holder').css('background-color', $(this).val());
    } );
    $('.template .background-1, .template .background-2, .template .background-3, .template .background-4').on('changeColor blur', function() {
        var num_id = $(this).attr('data-col-id');
        $(this).closest('.panel-body').find('.page-content-div-'+num_id).css('background-color', $(this).val());
    } );

    $('a.add-content').click( function() {
        var type = $(this).attr('data-type');
        var new_id = (Math.random().toString(36)+'00000000000000000').slice(2, 18);
        $(this).closest('.tab-pane').find('.content-blocks').append(
            $('.templates .template.template-'+type).clone(true, true).attr('id', new_id).show()
        );

        if(type=='add-html' || type=='add-html-2' || type=='add-html-3' || type=='add-html-4') {
            $('#'+new_id).find('.page-content-div').each( function() {
                var ck_id = (Math.random().toString(36)+'00000000000000000').slice(2, 18);
                $(this).attr('id', ck_id);
                CKEDITOR.inline( ck_id, ck_options );
            } );

            var pad = $('#'+new_id).find('.padding').val();
            console.log(pad);
            $('#'+new_id).find('.page-content-div').removeClass('p-none').removeClass('p-small').removeClass('p-medium').removeClass('p-big').removeClass('p-huge').addClass('p-'+pad);

            $('#'+new_id).find('.colorpicker-template').removeClass('colorpicker-template').addClass('colorpicker').colorpicker({format: 'rgba'});
        }
    } );


    $('form').off('submit').submit( function(e) {
        e.preventDefault();

        if(ajax_action) {
            return;
        }
        ajax_action = true;

        $('#error-message').hide();

        var re = /&nbsp;/gi;

        var form_data = getFormData( $(this) );
        for(var code in langs) {
            form_data['content-'+code] = [];
            $('#nav-tab-'+code).find('.content-blocks .panel').each( function() {
                if($(this).hasClass('template-add-html')) {
                    
                    form_data['content-'+code].push({
                        type: 'html',
                        content: CKEDITOR.instances[ $(this).find('.page-content-div').attr('id') ].getData().replace(re, " "),
                        padding: $(this).find('.padding').val(),
                        background: $(this).find('.background').val(),
                        class: $(this).find('.class').val(),
                        image: $(this).find('.background-image-input').val(),
                    });
                } else if($(this).hasClass('template-add-html-2')) {
                    
                    form_data['content-'+code].push({
                        type: 'html-2',
                        columns: [
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-1').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-1').val()
                            },
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-2').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-2').val()
                            }
                        ],
                        padding: $(this).find('.padding').val(),
                        background: $(this).find('.background-main').val(),
                        image: $(this).find('.background-image-input').val(),
                    });
                } else if($(this).hasClass('template-add-html-3')) {
                    
                    form_data['content-'+code].push({
                        type: 'html-3',
                        columns: [
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-1').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-1').val(),
                                class: $(this).find('.class-1').val(),
                            },
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-2').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-2').val(),
                                class: $(this).find('.class-2').val(),
                            },
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-3').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-3').val(),
                                class: $(this).find('.class-3').val(),
                            }
                        ],
                        padding: $(this).find('.padding').val(),
                        background: $(this).find('.background-main').val(),
                        image: $(this).find('.background-image-input').val(),
                    });
                } else if($(this).hasClass('template-add-html-4')) {
                    
                    form_data['content-'+code].push({
                        type: 'html-4',
                        columns: [
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-1').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-1').val()
                            },
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-2').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-2').val()
                            },
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-3').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-3').val()
                            },
                            {
                                content: CKEDITOR.instances[ $(this).find('.page-content-div-4').attr('id') ].getData().replace(re, " "),
                                background: $(this).find('.background-4').val()
                            }
                        ],
                        padding: $(this).find('.padding').val(),
                        background: $(this).find('.background-main').val(),
                        image: $(this).find('.background-image-input').val()
                    });
                } else if($(this).hasClass('template-add-map')) {
                    
                    form_data['content-'+code].push({
                        type: 'map',
                        address: $(this).find('.address').val()
                    });

                }

            } )
        }

        var form_data_real = new FormData();
        for( var i in form_data ) {
            if (form_data[i] instanceof Object || Array.isArray(form_data[i])) {
                form_data_real.append(i, JSON.stringify(form_data[i]));
            } else {
                form_data_real.append(i, form_data[i]);                
            }

        }
        if( typeof($('#image-input')[0].files[0]) != 'undefined' ) {
            form_data_real.append("image", $('#image-input')[0].files[0], 'image');
        }

        $.ajax({
            url     : $(this).attr('action'),
            type    : $(this).attr('method'),
            data    : form_data_real,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success : function( res ) {
                ajax_action = false;
                if(res && res.success) {
                    window.location.href = res.href;
                } else {
                    $('#error-message').html('').show();

                    for(var i in res.messages) {
                        $('#error-message').append(res.messages[i]+'<br/>');
                    }

                    $('html, body').animate({scrollTop: $("#error-message").offset().top}, 1000);
                }
            },
            error : function( data ) {
                ajax_action = false;
                $('#error-message').html('Network Error').show();
            }
        });
    });

});

function getFormData(form){
    var unindexed_array = form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function(n, i){
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}