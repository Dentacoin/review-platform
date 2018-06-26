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
	

	$('.question-number').on('keypress', function(e) {
	    var code = e.keyCode || e.which;
	    if (code == 13) {
	        $(this).blur();
	        return false;
	    }
	});

	$('.question-number').on('change blur', function() {
		console.log( $(this).attr('data-qid'), $(this).val() );

        if(ajax_action) {
            return;
        }
        ajax_action = true;
        $('.question-number').attr('disabled', 'disabled');

        $.ajax({
            url     : $('#page-add').attr('action') + '/change-number/'+$(this).attr('data-qid')+'/'+$(this).val(),
            type    : 'GET',
            dataType: 'json',
            success : function( res ) {
                ajax_action = false;
                var $trs = $('.table-question-list tbody tr');
                $trs.sort(function(a,b) {
					return parseInt($(a).find('.question-number').val()) < parseInt($(b).find('.question-number').val()) ? -1 : 1;
				})
				$trs.detach().appendTo( $('.table-question-list tbody') );
        		$('.question-number').removeAttr('disabled');
            },
            error : function( data ) {
                ajax_action = false;
        		$('.question-number').removeAttr('disabled');
            }
        });


	});
});