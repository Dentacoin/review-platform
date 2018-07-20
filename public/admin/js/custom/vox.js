$(document).ready(function(){

	var handleScaleChanges = function() {
		var qtype = $('.question-type-input').val();
		if(qtype!='scale' && $('.question-scale-input').val().length ) {
			$('.answers-group, .answers-group-add').hide();
		} else {
			$('.answers-group, .answers-group-add').show();
		}
	}

	$('.question-type-input, .question-scale-input').change(handleScaleChanges);
	handleScaleChanges();

	$('.questions-form .btn-add-answer').click( function() {
		$('.questions-form .tab-pane').each( function() {
			var code = $(this).attr('data-code');
			var newinput = $('#input-group-template').clone(true).removeAttr('id')
			newinput.find('input').attr('name', 'answers-'+code+'[]');
			$(this).find('.answers-list').append(newinput);
		} );
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
		$('.triggers-list .input-group').each( function() {
			if (!($(this).find('select').hasClass('select2'))) {
				$(this).find('select').addClass('select2');
				$(".select2").select2();
			}
		});
	} );

	$('.questions-form .btn-add-new-trigger').click( function() {
		var newinput = $('#new-trigger-group-template').clone(true).removeAttr('id');
		$('.questions-form').find('.triggers-list').append(newinput);
		$('.triggers-list .input-group').each( function() {
			if (!($(this).find('select').hasClass('select2'))) {
				$(this).find('select').addClass('select2');
				$(".select2").select2();
			}
		});
	} );


	$('.btn-remove-trigger').click( function() {
		$(this).closest('.input-group').remove();
	} );

	$(".select2").select2();


	controlQuestion = function() {
		if($('#is_control_prev').is(":checked")) {
			$('#is_control_prev').closest('.form-group').find('input[name="is_control"]').attr("disabled", true);
		} else {
			$('#is_control_prev').closest('.form-group').find('input[name="is_control"]').attr("disabled", false);
		}
	}
	controlQuestion();

	$('#is_control_prev').click( function() {
		controlQuestion();
	});

	$('.question-number, .question-question').on('keypress', function(e) {
	    var code = e.keyCode || e.which;
	    if (code == 13) {
	        $(this).blur();
	        return false;
	    }
	});

	$('.question-number, .question-question').on('change blur', function() {
		console.log( $(this).attr('data-qid'), $(this).val() );

        if(ajax_action) {
            return;
        }
        ajax_action = true;
        var urlpart;
        if( $(this).hasClass('question-question') ) {
        	$(this).attr('disabled', 'disabled');
        	urlpart = 'question';
        } else {
        	$('.question-number').attr('disabled', 'disabled');	
        	urlpart = 'number';
        }
        

        $.ajax({
            url     : $('#page-add').attr('action') + '/change-'+urlpart+'/'+$(this).attr('data-qid'),
            type    : 'POST',
            data 	: {
            	val: $(this).val()
            },
            dataType: 'json',
            success : (function( res ) {
                ajax_action = false;

                if( $(this).hasClass('question-number') ) {
	                var $trs = $('.table-question-list tbody tr');
	                $trs.sort(function(a,b) {
						return parseInt($(a).find('.question-number').val()) < parseInt($(b).find('.question-number').val()) ? -1 : 1;
					})
					$trs.detach().appendTo( $('.table-question-list tbody') );
                }
        		$('.question-number, .question-question').removeAttr('disabled');
            }).bind( this ),
            error : function( data ) {
                ajax_action = false;
        		$('.question-number, .question-question').removeAttr('disabled');
            }
        });


	});
	
});