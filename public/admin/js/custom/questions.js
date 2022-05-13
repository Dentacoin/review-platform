$(document).ready(function() {

	$('.add-answer').click( function() {
		var p = $(this).parent();
		var inputs = $('#answer-template .form-group').clone(true).appendTo( p ).find('input');
		var the_lang = $(this).closest('.lang-tab').attr('data-lang');
		inputs.each( function() {
			$(this).attr('name', $(this).attr('name').replace( 'code', the_lang ) );
		});

		p.find('.add-answer').appendTo( p );
		/*
		$('.answers-div').each( function() {
			$('#answer-template .form-group').clone(true).appendTo( $(this) );
			$(this).find('.add-answer').appendTo( $(this) );
		} )
		*/
	});

	$('.remove-answer').click( function() {
		$(this).closest('.form-group').remove();
	});

	$(".questions-draggable").sortable({
		update: function( event, ui ) {	
			console.log('update');
			setTimeout( function(){
				var ids = [];
				$('.questions-draggable tr').each( function() {
					ids.push( $(this).attr('question-id') );
				});

		        $.ajax({
		            url     : $(".questions-draggable").attr('url'),
		            type    : 'POST',
		            data 	: {
		            	list: ids
		            },
		            dataType: 'json',
		            success : (function( res ) {
		            	var i=1;
		            	$('.questions-draggable tr').each( function() {
							$(this).find('.question-number').html(i);
							console.log(i);
							i++;
						});
		            }).bind( this ),
		        });
			}, 0);
		},
	}).disableSelection();

});