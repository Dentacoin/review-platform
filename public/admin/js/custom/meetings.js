$(document).ready(function() {

	$('.btn-checklist-answer').click( function() {
        var newinput = $('#input-group-template').clone(true).removeAttr('id');
        newinput.find('.meeting-checklist').attr('name', 'checklists[]');
        $('.checklists-group').find('.checkist-list').append(newinput);
	});

	$('.btn-remove-checklist').click( function() {
		var group = $(this).closest('.input-group');
		var num = 1;
		var iterator = group;
		while( iterator.prev().length ) {
			iterator = iterator.prev();
			num++;
		}

		$('.checkist-list .input-group:nth-child('+num+')').remove();
	});

	$('.btn-after-checklist-answer').click( function() {
        var newinput = $('#input-group-template-afterlist').clone(true).removeAttr('id');
        newinput.find('.meeting-after-checklist').attr('name', 'after_checklist_info[]');
        $('.after-checklists-group').find('.checkist-list').append(newinput);
	});

	$('.btn-remove-checklist').click( function() {
		var group = $(this).closest('.input-group');
		var num = 1;
		var iterator = group;
		while( iterator.prev().length ) {
			iterator = iterator.prev();
			num++;
		}

		$('.checkist-list .input-group:nth-child('+num+')').remove();
	});
});

