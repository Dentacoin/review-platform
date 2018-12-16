$(document).ready(function(){

	$('.add-faq').click( function() {
		$('#faq-accordion').append( $('#accordion-template').html() );
		handleFaqQuestions();
	} );

	var handleFaqQuestions = function() {
		$('.btn-new-faq').off('click').click( function() {
			$(this).closest('.panel-body').find('.panel-group').append( $('#question-template').html() );
			handleFaqQuestions();
		} );

		$('.closer').off('click').click( function() {
			console.log('asasdsa');
			$(this).closest('.panel').remove();
		} );

		$( "#faq-accordion" ).sortable().disableSelection();
		$( ".main-panel .panel-group" ).sortable().disableSelection();

	}
	handleFaqQuestions();

	$('.save-faq').click( function() {
		var data = [];
		$('.main-panel').each( function() {

			if( $(this).closest('#accordion-template').length ) {
				return;
			}

			var section = {
				title: $(this).find('.section-title').val(),
				questions: []
			};

			$(this).find('.question-panel').each( function() {
				section.questions.push([
					$(this).find('input[type="text"]').val(),
					$(this).find('textarea').val(),
				])
			} );

			data.push(section);

		} );


        $.ajax({
            url     : window.location.href,
            type    : 'POST',
            data 	: {
            	'faq': data
            },
            dataType: 'json',
            success : (function( res ) {
                ajax_action = false;
                window.location.href = window.location.href;
            }).bind( this ),
            error : function( data ) {
                ajax_action = false;
                alert('Something went wrong!');
            }
        });

	} );
});