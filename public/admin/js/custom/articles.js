$(document).ready(function(){

	$('.add-answer').click( function() {
		var p = $(this).parent();
		console.log($(this).attr('data-name'));
		$('#answer-template .form-group').clone(true).appendTo( p ).find('input').attr('name', $(this).attr('data-name'));
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

