var dTable;
var handleFilters;
var sortMode = false;
$(document).ready(function(){

	//
	//Voxes list
	//

	if( $('#table-sort').length ) {

	    dTable = $('.table').DataTable({
	        "pageLength": 25
	    });

	    $('#table-sort, #table-sort-stats').click( function() {
	        if(sortMode) {
	            window.location.reload();
	            return;
	        }

            dTable.destroy();                
            sortMode = $(this).attr('id') == 'table-sort-stats' ? 'stats' : 'surveys';

            $('#table-sort').text( $(this).attr('alternate') );
	        $('#table-sort-stats').hide();


	        if( sortMode=='stats' ) {

		        var wrapper = $('.table tbody');
		        var list = wrapper.children('tr');
	            list.sort(function(a, b) {
	                if( parseInt($(a).children().first().next().text()) && parseInt($(a).children().first().next().text()) < parseInt($(b).children().first().next().text()) ) {
	                    return -1;
	                } else {
	                    return 1;
	                }
	            });

	            console.log(list);

	            list.each(function() {
	                wrapper.append(this);
	            });

	        }

            $('.table tbody').sortable({
                update: function( event, ui ) { 
                    console.log('update');
                    setTimeout( function(){
                        var ids = [];
                        $('.table tbody tr').each( function() {
                            ids.push( $(this).attr('item-id') );
                        } )

                        $.ajax({
                            url     : window.location.href + 'reorder',
                            type    : 'POST',
                            data    : {
                                list: ids,
                                stats: sortMode=='stats'
                            },
                            dataType: 'json',
                            success : (function( res ) {
                                var i=1;
                                $('.table tbody tr').each( function() {
                                	if( sortMode=='stats' ) {
                                    	$(this).find('td').first().next().text(i);
                                	} else {
                                    	$(this).find('td').first().text(i);
                                	}
                                    i++;
                                } )
                            }).bind( this ),
                            error : function( data ) {
                            }
                        });
                    }, 0);
                },
            }).disableSelection();
        });
	}



	//
	//Others
	//


	$('#explorer-question').change( function() {
		window.location.href = $(this).closest('form').attr('action') + '/' + $(this).closest('form').attr('vox-id') + '/' + $(this).val();
	} );

	$('#explorer-survey').change( function() {
		if ($(this).val()) {
			window.location.href = $(this).closest('form').attr('action') + '/' + $(this).val();
		}
	} );

	$('.toggler').change( function() {
		var id = $(this).attr('id');
		var field = $(this).attr('field');
        $.ajax({
            url     : 'cms/vox/edit-field/'+id+'/'+field+'/'+( $(this).is(':checked') ? 1 : 0 ),
            type    : 'GET'
        });

	} );

	// $('.order').click( function() {
	// 	// e.preventDefault();
	// 	var href = $(this).attr('href');

	// 	if ($(this).hasClass('asc')) {
	// 		$(this).removeClass('asc')
	// 		$(this).addClass('desc');
	// 		// window.location.href = href+'?country=desc';
	// 	} else {
	// 		$(this).addClass('asc');
	// 		$(this).removeClass('desc')
	// 		// window.location.href = href+'?country=asc';
	// 	}

	// });

	$('#select-cross').change( function() {
		var select_id = $(this).val();
		if (select_id) {
			$('#habits-table').show();
			$('.q-id').hide();
			$('.id-'+select_id).show();

			var i=0;
			$('[name="answers-en[]"]').each( function(){
				$($('.id-'+select_id)[i]).find('td:last-child').html( $(this).val() );
				i++;
			});
		}
		
	});

	if ($('#select-cross').length) {
		$('#select-cross').trigger('change');
	}


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
			$('.stat_title').show();
			$('#stat_standard').hide();
		} else if(stats=='standard') {
			$('#stat_relations').hide();
			$('.stat_title').show();
			$('#stat_standard').show();
		} else {
			$('#stat_standard').hide();
			$('#stat_relations').hide();
			$('.stat_title').hide();
		}
	}

	$('.question-type-input, .question-scale-input, .question-stats-input').change(handleScaleChanges);
	handleScaleChanges();

	$('.questions-form .btn-add-answer').click( function() {
		$('.questions-form .questions-pane').each( function() {
			var code = $(this).attr('lang');
			var newinput = $('#input-group-template').clone(true).removeAttr('id')
			newinput.find('input.answer-name').attr('name', 'answers-'+code+'[]');
			newinput.find('input.answer-tooltip').attr('name', 'answers_tooltips-'+code+'[]');
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

	$('.add-related').click( function() {
		var p = $(this).closest('.related-group').find('.related-list .input-group');
		$('#related-template .form-group').clone(true, true).appendTo( p );

		console.log('ffffff');
		$('.related-group .input-group .form-group').each( function() {
			if (!($(this).find('select').hasClass('select2'))) {
				$(this).find('select').addClass('select2');
				$(".select2").select2();
			}
		});
		/*
		$('.answers-div').each( function() {
			$('#answer-template .form-group').clone(true).appendTo( $(this) );
			$(this).find('.add-answer').appendTo( $(this) );
		} )
		*/
	} );

	$('.remove-related').click( function() {
		$(this).closest('.form-group').remove();
	} );


	$('.questions-form .btn-add-trigger').click( function() {
		var newinput = $('#trigger-group-template').clone(true).removeAttr('id');
		$('.triggers-list .input-group').remove();
		$('.questions-form').find('.triggers-list').append(newinput);
		$('.triggers-list .input-group').each( function() {
			if (!($(this).find('select').hasClass('select2'))) {
				$(this).find('select').addClass('select2');
				$(".select2").select2();
			}
		});

		$('.questions-form .btn-add-trigger').hide();
		$('.questions-form .btn-add-new-trigger').hide();
		$('.questions-form .btn-add-old-trigger').hide();
	} );

	$('.questions-form .btn-add-old-trigger').click( function() {
		var newinput = $('#old-trigger-group-template').clone(true).removeAttr('id');
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
		if( $(this).closest('.same-as-before').length ) {
			$('.questions-form .btn-add-trigger').show();
			$('.questions-form .btn-add-old-trigger').show();
			$('.questions-form .btn-add-new-trigger').show();
		}

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



	var symbolsCount = function() {
		var parent = $(this).closest('.col-md-4');
		var length = $(this).val().length;
		parent.find('.textarea-symbols .symbol-count').html(length);

		if (length > parseInt(parent.attr('max-symb'))) {
			parent.find('.textarea-symbols .symbol-count').css('color', 'red');
		} else {
			parent.find('.textarea-symbols .symbol-count').css('color', '#707478');
		}
	}

	$('#surv-desc').keyup(symbolsCount);
	if( $('#surv-desc').length ) {
		symbolsCount.bind($('#surv-desc'))();
	}


	

});