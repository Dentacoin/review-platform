$(document).ready(function(){

	$('.register-type').change( function(){
	    $('#register-form').show();
	    $(this).closest('.btn-group').find('.btn').removeClass('btn-primary').addClass('btn-default');
	    $(this).closest('.btn').addClass('btn-primary').removeClass('btn-default');
	    $(this).closest('.btn').blur();
	});

	$('.register-social').click( function(e) {
		e.preventDefault();
		window.location.href = $(this).attr('href') + '/' + $('.register-type:checked').val();
	});
});