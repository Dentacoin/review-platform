$(document).ready(function(){
	$('#countries-change').submit( function(e) {
		e.preventDefault();
		window.location.href = '/admin/'+current_page+'/'+$('#translate-from').val()+'/'+$('#translate-to').val()
	});
});