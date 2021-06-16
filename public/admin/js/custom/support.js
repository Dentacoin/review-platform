$(document).ready(function(){

 //    $('.textarea-ckeditor').click( function() {
	// 	CKEDITOR.replace( $(this).attr('id') );

	// 	// $(this).next().show();
	// });

	if($('#answer').length) {
		CKEDITOR.replace( 'answer' );
	}

	$( "#question" ).on('keyup', function() {
		$( "#slug" ).val( convertToSlug($(this).val()) );
	});

	$('#generate-slug').click( function() {
		$( "#edit-slug" ).val( convertToSlug($( "#edit-question" ).val()) );
	});

	function convertToSlug( str ) {
		//replace all special characters | symbols with a space
		str = str.replace(/[`~!@#$%^&*()_\-+=\[\]{};:'"\\|\/,.<>?\s]/g, ' ').toLowerCase();
		// trim spaces at start and end of string
		str = str.replace(/^\s+|\s+$/gm,'');
		// replace space with dash/hyphen
		str = str.replace(/\s+/g, '-');	
		// document.getElementById("slug-text").innerHTML= str;
		return str;
	}

	$('#add-support-question').submit( function(e) {
		e.preventDefault();

		$('#question-error').hide();
        var question = $( "#question" ).val();
        var answer = $( "#answer" ).val();
        var slug = $( "#slug" ).val();
        var is_main = $( "#is_main" ).is(':checked');
        var category = $( "#question-category option:selected" ).val();
        var edit_url = $(this).attr('edit-url');
        var delete_url = $(this).attr('delete-url');

	    $( "#answer" ).val(CKEDITOR.instances.answer.getData());
        var formData = new FormData(this);

        $.ajax({
	        url: $(this).attr('action'),
	        type: 'POST',
	        data: formData,
	        cache: false,
	        contentType: false,
	        processData: false
	    }).done( (function (data) {

			if(data.success) {
				$('.nav-tabs li').removeClass('active');
				$('.nav-tabs').find('a[href="#nav-tab-'+category+'"]').closest('li').addClass('active');
				$('.tab-pane.fade').removeClass('active');
				$('.tab-pane.fade').removeClass('in');
				$('#nav-tab-'+category).addClass('active in');

                    // <td>'+answer+'</td>\
				$('#nav-tab-'+category).find('tbody').append('<tr>\
                    <td>'+data.order+'</td>\
                    <td>'+question+'</td>\
                    <td>'+(is_main ? 'Yes' : '')+'</td>\
                    <td>\
                        <a class="btn btn-sm btn-primary" href="'+edit_url+'/'+data.q_id+'/">Edit</a>\
                    </td>\
                    <td>\
                        <a class="btn btn-sm btn-deafult delete-question" href="'+delete_url+'/'+data.q_id+'/" onclick="return confirm(\'Are you sure you want to DELETE this?\');">Delete</a>\
                    </td>\
            	</tr>');

            	$('#add-support-question')[0].reset();
            	// CKEDITOR.instances.answer.destroy();
            	CKEDITOR.instances.answer.setData('');
			} else {
				var error = '';

				for(var i in data.messages) {
					error+= data.messages[i] +'<br/>';
				}

				$('#question-error').html(error).show();
			}

	    }).bind(this) ).fail(function (data) {
			console.log(data);
	    });
	});

	$('.delete-question').click( function(e) {
		e.preventDefault();
		
		$.ajax({
	        url: $(this).attr('href'),
	        type: 'POST',
	        cache: false,
	        contentType: false,
	        processData: false
	    }).done( (function (data) {
			if(data.success) {
				$(this).closest('tr').remove();
			}
	    }).bind(this) ).fail(function (data) {
			console.log(data);
	    });
	});

	$('.answer-contact').click( function() {

		var action = $('#answerModal form').attr('original-action') + '/' + $(this).attr('contact-id');
		$('#answerModal form').attr('action' , action);
		$('#answerModal form').attr('contact-id' , $(this).attr('contact-id'));
	});

	if ($('.select2').length) {
        $(".select2").select2({
            placeholder: 'Select Template',
        });
    }

	$('.contact-form').submit( function(e) {
        e.preventDefault();

        $(this).find('.contact-error').hide();
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
				$('tr[contact-id="'+$(this).attr('contact-id')+'"]').find('.actions').html('Sended');
			} else {
				$(this).find('.contact-error').html(data.message);
				$(this).find('.contact-error').show();
			}

	    }).bind(this) ).fail(function (data) {
			console.log(data);
	    });

    } );

    $('.show-answer').click( function(e) {
    	e.preventDefault();
    	$(this).next().show();
    	$(this).hide();
    });

    $( ".questions-draggable" ).sortable({
		update: function( event, ui ) {	
			// console.log($(this));
			var ids = [];
			$(this).find('tr').each( function() {
				ids.push( $(this).attr('question-id') );
			} );

	        $.ajax({
	            url     : $(this).attr('reorder-url'),
	            type    : 'POST',
	            data 	: {
	            	list: ids
	            },
	            dataType: 'json',
	            success : (function( res ) {
	            	var i=1;
	            	$(this).find('tr').each( function() {
						$(this).find('.question-number').html(i);
						i++;
					} )
	            }).bind( this ),
	            error : function( data ) {
	            }
	        });
		},
	}).disableSelection();
});

