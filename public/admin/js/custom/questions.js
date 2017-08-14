var ajax_active = false;
$(document).ready(function(){

	$('.add-answer').click( function() {
		var p = $(this).parent();
		console.log($(this).attr('data-name'));
		var inputs = $('#answer-template .form-group').clone(true).appendTo( p ).find('input');
		var the_lang = $(this).closest('.lang-tab').attr('data-lang');
		console.log(the_lang);
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
	} );

	$('.remove-answer').click( function() {
		$(this).closest('.form-group').remove();
	} );

});