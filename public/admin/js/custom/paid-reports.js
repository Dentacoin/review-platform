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

	$('#generate-slug').click( function() {
		console.log(convertToSlug($( "#edit-main-title" ).val())+convertToSlug($( "#edit-title" ).val()));
		$( "#edit-slug" ).val( convertToSlug($( "#edit-main-title" ).val())+'-'+convertToSlug($( "#edit-title" ).val()) );
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

		$('.contents-list .input-group:nth-child('+num+')').remove();
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
});

