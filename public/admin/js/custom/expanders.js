$(document).ready(function(){
	$('.country a.expander').click( function() {
		var id = $(this).attr('data-id');

		var other_text = $(this).attr('data-other');
		$(this).attr('data-other', $(this).html());
		$(this).html(other_text);

		$('.state-'+id).toggle();
	} );

	$('.state a.expander').click( function() {
		var id = $(this).attr('data-id');
		
		var other_text = $(this).attr('data-other');
		$(this).attr('data-other', $(this).html());
		$(this).html(other_text);

		$('.city-'+id).toggle();
	} );

	$('.city a.expander').click( function() {
		var id = $(this).attr('data-id');
		
		var other_text = $(this).attr('data-other');
		$(this).attr('data-other', $(this).html());
		$(this).html(other_text);
		
		$('.district-'+id).toggle();
	} );
});