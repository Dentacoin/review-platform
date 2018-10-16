$(document).ready(function(){


	$('.toggler').change( function() {
		var id = $(this).attr('id');
		var field = $(this).attr('field');
        $.ajax({
            url     : 'cms/vox/edit-field/'+id+'/'+field+'/'+( $(this).is(':checked') ? 1 : 0 ),
            type    : 'GET'
        });

	} );


	var handleScaleChanges = function() {
		var qtype = $('.question-type-input').val();
		if(!qtype) {
			return;
		}

		if(qtype!='scale' && $('.question-scale-input').val().length ) {
			$('.answers-group, .answers-group-add').hide();
		} else {
			$('.answers-group, .answers-group-add').show();
		}

		var stats = $('.question-stats-input').val();
		if(stats=='dependency') {
			$('#stat_relations').show();
			$('#stat_title').show();
			$('#stat_standard').hide();
		} else if(stats=='standard') {
			$('#stat_relations').hide();
			$('#stat_title').show();
			$('#stat_standard').show();
		} else {
			$('#stat_standard').hide();
			$('#stat_relations').hide();
			$('#stat_title').hide();
		}
	}

	$('.question-type-input, .question-scale-input, .question-stats-input').change(handleScaleChanges);
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
	


	$( ".answers-draggable" ).sortable().disableSelection();
	$( ".questions-draggable" ).sortable({
		update: function( event, ui ) {	
			console.log('update');
			setTimeout( function(){
				var ids = [];
				$('.questions-draggable tr').each( function() {
					ids.push( $(this).attr('question-id') );
				} )

		        $.ajax({
		            url     : $('#page-add').attr('action') + '/change-all',
		            type    : 'POST',
		            data 	: {
		            	list: ids
		            },
		            dataType: 'json',
		            success : (function( res ) {
		            	var i=1;
		            	$('.questions-draggable tr').each( function() {
							$(this).find('.question-number').val(i);
							i++;
						} )
		            }).bind( this ),
		            error : function( data ) {
		            }
		        });
			}, 0);
		},
	}).disableSelection();

	$('.add-faq').click( function() {
		$('#faq-accordion').append( $('#accordion-template').html() );
		handleFaqQuestions();
	} );

	var handleFaqQuestions = function() {
		$('.btn-new-faq').off('click').click( function() {
			$(this).closest('.panel-body').find('.panel-group').append( $('#question-template').html() );
		} );
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