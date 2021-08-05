$(document).ready(function(){

	if($('#paid-report-short-descr').length) {
		CKEDITOR.replace( 'paid-report-short-descr' );
	}

	if($('#methodology').length) {
		CKEDITOR.replace( 'methodology' );
	}

	if($('#summary').length) {
		CKEDITOR.replace( 'summary' );
	}

	

	// $( "#edit-title" ).on('keyup', function() {
	// 	$( "#edit-slug" ).val( convertToSlug($(this).val()) );
	// });

	$('#generate-slug').click( function() {
		$( "#edit-slug" ).val( convertToSlug($( "#edit-title" ).val()) );
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

	$('.btn-checklist-answer').click( function() {
		$('.checkists-pane').each( function() {
			var code = $(this).attr('lang');
			var newinput = $('#input-group-template').clone(true).removeAttr('id')
			newinput.find('.paid-checklist').attr('name', 'checklists-'+code+'[]');
			$(this).find('.checkist-list').append(newinput);
		} );
	} );

	$('.btn-remove-checklist').click( function() {
		var group = $(this).closest('.input-group');
		console.log(group);
		var num = 1;
		var iterator = group;
		while( iterator.prev().length ) {
			console.log( iterator.prev() );
			iterator = iterator.prev();
			num++;
		}

		console.log(num);

		$('.checkist-list .input-group:nth-child('+num+')').remove();
	} );

	$('.btn-add-table-contents').click( function() {
		$('.contents-pane').each( function() {
			var code = $(this).attr('lang');
			var newinput = $('#input-group-templatee').clone(true).removeAttr('id')
			newinput.find('.table-contents').attr('name', 'table_contents-'+code+'[]');
			newinput.find('.main-contents').attr('name', 'main-'+code+'[]');
			newinput.find('.page-contents').attr('name', 'page-'+code+'[]');
			$(this).find('.contents-list').append(newinput);
		} );
	} );

	$(".reports-form").submit(function () {

		var this_master = $(this);
		this_master.find('.is-main').each( function () {
			var checkbox_this = $(this);
			if( checkbox_this.is(":checked") == true ) {
				checkbox_this.attr('value','1');
			} else {
				checkbox_this.prop('checked',true);
				//DONT' ITS JUST CHECK THE CHECKBOX TO SUBMIT FORM DATA    
				checkbox_this.attr('value','0');
			}
		});
	});

	$('.btn-remove-contents').click( function() {
		var group = $(this).closest('.input-group');
		console.log(group);
		var num = 1;
		var iterator = group;
		while( iterator.prev().length ) {
			console.log( iterator.prev() );
			iterator = iterator.prev();
			num++;
		}

		console.log(num);

		$('.sample-list .input-group:nth-child('+num+')').remove();
	} );

	$( ".contents-draggable" ).sortable().disableSelection();

	$('.btn-add-sample-page').click( function() {
		$('.sample-list').append($('#input-group-sample-pages').clone(true).removeAttr('id'));
	} );


	$('.btn-remove-sample-page').click( function() {
		$(this).closest('.input-group').remove();
	} );

	$('.btn-delete-sample-page').click( function(e) {
		e.preventDefault();
		var that = $(this);

		$.ajax({
			url: that.attr('url'),
			type: 'POST',
			cache: false,
			contentType: false,
			processData: false,
			data: {
				photo_id: that.attr('photo-id')
			},
		}).done(function (data) {
			if(data.success) {
				that.closest('.input-group').remove();
			}
		}).fail(function (data) {
			console.log(data);
		});
	} );
	

	// $('#add-support-question').submit( function(e) {
	// 	e.preventDefault();

	// 	$('#question-error').hide();
    //     var question = $( "#question" ).val();
    //     var answer = $( "#answer" ).val();
    //     var slug = $( "#slug" ).val();
    //     var is_main = $( "#is_main" ).is(':checked');
    //     var category = $( "#question-category option:selected" ).val();
    //     var edit_url = $(this).attr('edit-url');
    //     var delete_url = $(this).attr('delete-url');

	//     $( "#answer" ).val(CKEDITOR.instances.answer.getData());
    //     var formData = new FormData(this);

    //     $.ajax({
	//         url: $(this).attr('action'),
	//         type: 'POST',
	//         data: formData,
	//         cache: false,
	//         contentType: false,
	//         processData: false
	//     }).done( (function (data) {

	// 		if(data.success) {
	// 			$('.nav-tabs li').removeClass('active');
	// 			$('.nav-tabs').find('a[href="#nav-tab-'+category+'"]').closest('li').addClass('active');
	// 			$('.tab-pane.fade').removeClass('active');
	// 			$('.tab-pane.fade').removeClass('in');
	// 			$('#nav-tab-'+category).addClass('active in');

    //                 // <td>'+answer+'</td>\
	// 			$('#nav-tab-'+category).find('tbody').append('<tr>\
    //                 <td>'+data.order+'</td>\
    //                 <td>'+question+'</td>\
    //                 <td>'+(is_main ? 'Yes' : '')+'</td>\
    //                 <td>\
    //                     <a class="btn btn-sm btn-primary" href="'+edit_url+'/'+data.q_id+'/">Edit</a>\
    //                 </td>\
    //                 <td>\
    //                     <a class="btn btn-sm btn-deafult delete-question" href="'+delete_url+'/'+data.q_id+'/" onclick="return confirm(\'Are you sure you want to DELETE this?\');">Delete</a>\
    //                 </td>\
    //         	</tr>');

    //         	$('#add-support-question')[0].reset();
    //         	// CKEDITOR.instances.answer.destroy();
    //         	CKEDITOR.instances.answer.setData('');
	// 		} else {
	// 			var error = '';

	// 			for(var i in data.messages) {
	// 				error+= data.messages[i] +'<br/>';
	// 			}

	// 			$('#question-error').html(error).show();
	// 		}

	//     }).bind(this) ).fail(function (data) {
	// 		console.log(data);
	//     });
	// });

	// $('.delete-question').click( function(e) {
	// 	e.preventDefault();
		
	// 	$.ajax({
	//         url: $(this).attr('href'),
	//         type: 'POST',
	//         cache: false,
	//         contentType: false,
	//         processData: false
	//     }).done( (function (data) {
	// 		if(data.success) {
	// 			$(this).closest('tr').remove();
	// 		}
	//     }).bind(this) ).fail(function (data) {
	// 		console.log(data);
	//     });
	// });

	// $('.answer-contact').click( function() {

	// 	var action = $('#answerModal form').attr('original-action') + '/' + $(this).attr('contact-id');
	// 	$('#answerModal form').attr('action' , action);
	// 	$('#answerModal form').attr('contact-id' , $(this).attr('contact-id'));
	// });

	// if ($('.select2').length) {
    //     $(".select2").select2({
    //         placeholder: 'Select Template',
    //     });
    // }

	// $('.contact-form').submit( function(e) {
    //     e.preventDefault();

    //     $(this).find('.contact-error').hide();
    //     var formData = new FormData(this);

    //     $.ajax({
	//         url: $(this).attr('action'),
	//         type: 'POST',
	//         data: formData,
	//         cache: false,
	//         contentType: false,
	//         processData: false
	//     }).done( (function (data) {
	// 		console.log(data);

	// 		if(data.success) {
	// 			$('.modal').modal('hide');
	// 			$('tr[contact-id="'+$(this).attr('contact-id')+'"]').find('.actions').html('Sended');
	// 		} else {
	// 			$(this).find('.contact-error').html(data.message);
	// 			$(this).find('.contact-error').show();
	// 		}

	//     }).bind(this) ).fail(function (data) {
	// 		console.log(data);
	//     });

    // } );

    // $('.show-answer').click( function(e) {
    // 	e.preventDefault();
    // 	$(this).next().show();
    // 	$(this).hide();
    // });

    // $( ".questions-draggable" ).sortable({
	// 	update: function( event, ui ) {	
	// 		// console.log($(this));
	// 		var ids = [];
	// 		$(this).find('tr').each( function() {
	// 			ids.push( $(this).attr('question-id') );
	// 		} );

	//         $.ajax({
	//             url     : $(this).attr('reorder-url'),
	//             type    : 'POST',
	//             data 	: {
	//             	list: ids
	//             },
	//             dataType: 'json',
	//             success : (function( res ) {
	//             	var i=1;
	//             	$(this).find('tr').each( function() {
	// 					$(this).find('.question-number').html(i);
	// 					i++;
	// 				} )
	//             }).bind( this ),
	//             error : function( data ) {
	//             }
	//         });
	// 	},
	// }).disableSelection();
});

