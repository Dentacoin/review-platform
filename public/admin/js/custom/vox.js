$(document).ready(function(){

	$('.questions-form .btn-add-answer').click( function() {
		$('.questions-form .tab-pane').each( function() {
			var code = $(this).attr('data-code');
			var newinput = $('#input-group-template').clone(true).removeAttr('id')
			newinput.find('input').attr('name', 'answers-'+code+'[]');
			$(this).find('.answers-list').append(newinput);
		} )
	} );

	$('.btn-remove-answer').click( function() {
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

		$('.answers-list .input-group:nth-child('+num+')').remove();
	} );


	$('.questions-form .btn-add-trigger').click( function() {
		var newinput = $('#trigger-group-template').clone(true).removeAttr('id');
		$('.questions-form').find('.triggers-list').append(newinput);
	} );


	$('.btn-remove-trigger').click( function() {
		$(this).closest('.input-group').remove();
	} );
	
});