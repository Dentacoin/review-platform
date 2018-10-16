$(document).ready(function(){

	// :D
	var handleBlueBalls = function() {
		$('.ball').each( function() {
			console.log(  $(this).height() );
			$(this).css('width', $(this).height() );
		} )
	}
	handleBlueBalls();
	$(window).resize(handleBlueBalls);

});
