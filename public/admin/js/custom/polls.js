$(document).ready(function(){

	$('.polls-form .btn-add-answer').click( function() {
		$('.polls-form .questions-pane').each( function() {
			var code = $(this).attr('lang');
			var newinput = $('#input-group-template').clone(true).removeAttr('id')
			newinput.find('input.answer-name').attr('name', 'answers-'+code+'[]');
			$(this).find('.answers-list').append(newinput);
		} );
	} );

	$( ".polls-draggable" ).sortable().disableSelection();

	

	$('.poll-date, .poll-question').on('change blur', function() {

        setTimeout( (function() {

    		console.log( $(this).attr('data-qid'), $(this).val() );

            if(ajax_action) {
                return;
            }
            ajax_action = true;
            var urlpart;
            if( $(this).hasClass('poll-question') ) {
            	urlpart = 'question';
            } else {
            	urlpart = 'date';
            }
            

            $.ajax({
                url     : $('#polls-actions').attr('action') + '/change-'+urlpart+'/'+$(this).attr('data-qid'),
                type    : 'POST',
                data 	: {
                	val: $(this).val()
                },
                dataType: 'json',
                success : (function( res ) {
                    ajax_action = false;
                }).bind( this ),
                error : function( data ) {
                    ajax_action = false;
                }
            });

        }).bind(this), 100 );
	});

	$('.polldatepicker').datepicker({
        todayHighlight: true,
        dateFormat: 'yy-mm-dd',
    });

    $('.poll-answers').on('keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            $(this).closest('.questions-pane').find('.btn-add-answer').trigger('click');
            $(this).closest('.answers-list').find('.poll-answers').last().focus();
        }
    });

    var handleScaleChanges = function() {

        if($('.scale-input').val() ) {
            $('.answers-group, .answers-group-add').hide();
        } else {
            $('.answers-group, .answers-group-add').show();
        }
    }

    $('.scale-input').change(handleScaleChanges);
    handleScaleChanges();


    $('#excell-poll-answers').click( function(e) {
        e.preventDefault();

        if($('#excell_answers').val()) {
            var val = $('#excell_answers').val().replace('\n', '<br/>');
            textarea_val = val.split('<br/>');
            for( var i in textarea_val) {
                $('.polls-form .questions-pane').each( function() {
                    var code = $(this).attr('lang');
                    var newinput = $('#input-group-template').clone(true).removeAttr('id')
                    newinput.find('input.answer-name').attr('name', 'answers-'+code+'[]');
                    newinput.find('input').val(textarea_val[i]);
                    $(this).find('.answers-list').append(newinput);
                } );
            }
        }
    });
});