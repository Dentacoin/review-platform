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

	    $('#table-sort').click( function() {
	        if(sortMode) {
	            window.location.reload();
	            return;
	        }

            dTable.destroy(); 
            sortMode = true;

            $('#table-sort').text( $(this).attr('alternate') );

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
                            },
                            dataType: 'json',
                            success : (function( res ) {
                                var i=1;
                                $('.table tbody tr').each( function() {
                                    $(this).find('td').first().text(i);
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

		// if(qtype!='scale' && $('.question-scale-input').val().length) {
		// 	$('.answers-group, .answers-group-add').hide();
		// } else {
		// 	$('.answers-group, .answers-group-add').show();
		// }

		if(qtype!='scale' && $('.question-scale-input').val().length) {
			$('.answers-group, .answers-group-add').hide();
		} else {
			if (qtype == 'number') {
				$('.answers-group, .answers-group-add').hide();
			} else {
				$('.answers-group, .answers-group-add').show();
			}
		}

		if(qtype == 'multiple_choice') {
			$('#exclude-answers').show();
		} else {
			$('#exclude-answers').hide();
		}

		if(qtype == 'number') {
			$('.answers-randomize').hide();
			$('.question-scale-wrapper').hide();
			$('.question-number-wrapper').show();
			$('.question-control-wrap').hide();
			$('.hint-for-scale').hide();
			$('.question-control-wrap').hide();
			$('.rank-explanation').hide();
		} else {
			if(qtype == 'rank') {
				$('.answers-randomize').hide();
				$('.question-scale-wrapper').hide();
				$('.hint-for-scale').hide();
				$('.question-control-wrap').hide();
				$('.rank-explanation').show();
			} else {
				$('.answers-randomize').show();
				$('.question-scale-wrapper').show();
				$('.hint-for-scale').show();
				$('.question-control-wrap').show();
				$('.rank-explanation').hide();
			}
			
			$('.question-number-wrapper').hide();
		}

		if (qtype == 'single_choice') {
			$('#randomize-single').show();
		} else {
			$('#randomize-single').hide();
		}

		var stats = $('.question-stats-input').val();
		if(stats=='dependency') {
			$('#stat_relations').show();
			$('.stat_title').show();
			$('#stat_standard').show();
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
			$(this).find('.answers-list').append(newinput);

			if ($(this).find('.answers-list').children().length > 10) {
				$(this).find('.answers-error').show();
			}
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
		$('#translate-answers').val('1');
	} );


	if ($('.related-group').length && $('.related-group').find('.related-list .input-group').children().length >=6) {
		$('.add-related').hide();
	}

	$('.add-related').click( function() {
		var p = $(this).closest('.related-group').find('.related-list .input-group');
		$('#related-template .form-group').clone(true, true).appendTo( p );
		$('.related-group .input-group .form-group').each( function() {
			if (!($(this).find('select').hasClass('select2'))) {
				$(this).find('select').addClass('select2');
				$(".select2").select2();
			}
		});
		if( p.children().length >=6) {
			$(this).hide();
			
		}
	} );

	$('.remove-related').click( function() {
		$(this).closest('.form-group').remove();

		var p = $(this).closest('.related-group').find('.related-list .input-group');
		if( p.children().length < 6) {
			$('.add-related').show();
			
		}
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
		$('.triggers-list .input-group').each( function() {
			$(this).find('.btn-remove-trigger').trigger('click');
		});
		var newinput = $('#old-trigger-group-template').clone(true).removeAttr('id');
		$('.questions-form').find('.triggers-list').append(newinput);
		$('.triggers-list .input-group').each( function() {
			if (!($(this).find('select').hasClass('select2'))) {
				$(this).find('select').addClass('select2');
				$(".select2").select2();
			}
		});

		$('[name="trigger_type"]').prop('checked', false);
		$('[name="trigger_type"][value="'+$('#old-trigger-type').val()+'"]').prop('checked', 'checked');		
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

	$('.question-number').on('keypress', function(e) {
	    var code = e.keyCode || e.which;
	    if (code == 13) {
	        $(this).blur();
	        return false;
	    }
	});

	$('.question-number, .question-question').on('change blur', function() {
		//console.log( $(this).attr('data-qid'), $(this).val() );

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
	


	$( ".answers-draggable" ).sortable({
		update: function( event, ui ) {	

			if ($(ui.item[0]).attr('answer-code') == 'en') {
				$('#translate-answers').val('1');
			}

		// 	var dragged_item_order = $(ui.item[0]).index();
		// 	// console.log(dragged_item_order);

		// 	var other_langs_aswers = $(".answers-draggable").not($(ui.item[0]).parent());

		// 	if(other_langs_aswers.length) {

		// 		other_langs_aswers.each( function() {
		// 			console.log($(this).find('.input-group:eq('+dragged_item_order+')'));
		// 			$(this).find('.input-group:nth-child('+(dragged_item_order-1)+')').insertAfter($(this).find('.input-group:eq('+dragged_item_order+')'));
		// 		});
		// 	}

		// 	console.log( $(ui.item[0]).index());
			
		// 	console.log( 'update');
		},
	}).disableSelection();

	$('[name="answers-en[]"]').on('keypress change', function() {
		$('#translate-answers').val('1');
	});

	$('[name="question-en"]').on('keypress change', function() {
		$('#translate-question').val('1');
	});	
	
	$('.questions-draggable[lang-code="en"]').multisortable({
		items: "tr",
		selectedClass: "selected",
		click: function(e) { 
			$('.questions-draggable[lang-code="en"]').find("textarea").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
		      	e.stopImmediatePropagation();
		    });
			$('.questions-draggable[lang-code="en"]').find("input").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
		      	e.stopImmediatePropagation();
		    });
		},
		update: function( event, ui ) {	
			console.log('update');
			setTimeout( function(){
				var ids = [];
				$('.questions-draggable[lang-code="en"] tr').each( function() {
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
		            	$('.questions-draggable[lang-code="en"] tr').each( function() {
							$(this).find('.question-number').val(i);
							i++;
						} )
		            }).bind( this ),
		            error : function( data ) {
		            }
		        });
			}, 0);
		},
	});

	$(".questions-draggable").find("textarea").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
	  	e.stopImmediatePropagation();
	});

	$(".questions-draggable").find("input").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
	  	e.stopImmediatePropagation();
	});

	
	// $( ".questions-draggable" ).sortable({
	// 	update: function( event, ui ) {	
	// 		console.log('update');
	// 		setTimeout( function(){
	// 			var ids = [];
	// 			$('.questions-draggable tr').each( function() {
	// 				ids.push( $(this).attr('question-id') );
	// 			} )

	// 	        $.ajax({
	// 	            url     : $('#page-add').attr('action') + '/change-all',
	// 	            type    : 'POST',
	// 	            data 	: {
	// 	            	list: ids
	// 	            },
	// 	            dataType: 'json',
	// 	            success : (function( res ) {
	// 	            	var i=1;
	// 	            	$('.questions-draggable tr').each( function() {
	// 						$(this).find('.question-number').val(i);
	// 						i++;
	// 					} )
	// 	            }).bind( this ),
	// 	            error : function( data ) {
	// 	            }
	// 	        });
	// 		}, 0);
	// 	},
	// }).disableSelection();

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


	var triggerClick =  function() {
		$('#close-and-add-trigger').click( function() {
			$(this).closest('#trigger-widgets').find('.button-close-trigger').trigger('click');
			$('.btn-add-old-trigger').trigger('click');
			$('.show-me').hide();
		});
	}

	$('.show-trigger-controls').click( function() {
		$('#trigger-widgets').show();
		$('#trigger-widgets').prev().remove();
		$('.btn-add-trigger').trigger('click'); 
		$('.show-me').show();
		triggerClick();
	});

	$('.diplicate-q-button').click( function() {
		console.log($(this).attr('q-id'));
		$('#d-question').val($(this).attr('q-id'));
	});

	$('.target-button').click( function() {
		$(this).parent().find('.target-wrapper').show();
		$(this).hide();
	});

	$('.triggers-button').click( function() {
		$(this).parent().find('.calculating-wrapper').show();
		$(this).hide();
	});

	$('input[name="country_percentage"]').on('wheel.disableScroll', function (e) {
		e.preventDefault()
	});
	

	// $('#manually-calc-reward').change( function() {
	// 	if ($(this).is(':checked')) {
	// 		$('.calculating-wrapper').show();
	// 	} else {
	// 		$('.calculating-wrapper').hide();
	// 	}
		
	// });

	// if($('#manually-calc-reward:checked').length) {
	// 	$('.calculating-wrapper').show();
	// }


	$('#search-questions').on('keyup keypress', function(e) {

        var query = $(this).val();

		$('.search-questions-wrapper .results').show();
		$('.search-questions-wrapper .results').html('');
		$('.search-questions-wrapper .results .result').remove();
		$('.search-questions-wrapper .results .loader').remove();
        $('.search-questions-wrapper .results').append('<div class="loader"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>')

		if(query.length >= 3) {

	        $.ajax( {
				url: $(this).attr('url'),
				type: 'POST',
				data: {
					title: query
				},
				dataType: 'json',
				success: function( data ) {
					$('.search-questions-wrapper .results .result').remove();
					$('.search-questions-wrapper .results .loader').remove();

					var count = $.map(data, function(n, i) { return i; }).length;

					if(count) {
						for(var i in data) {
							if(data[i].questions) {
								var qs = '';
								for( var q in data[i].questions) {
									qs+='<a href="'+data[i].questions[q].link+'">'+data[i].questions[q].name+'</a>';
								}
							}

							$('.search-questions-wrapper .results').append('\
								<div class="result">\
									<a target="_blank" href="'+data[i].link+'">'+data[i].name+'</a><div id="q'+i+'" class="questions-titles"></div>\
								</div>\
							');

							for( var q in data[i].questions) {
								$('#q'+i).append('<a target="_blank" href="'+data[i].questions[q].link+'">'+data[i].questions[q].name+'</a>');
							}
						}
					} else {
						$('.search-questions-wrapper .results .result').remove();
						$('.search-questions-wrapper .results .result').hide();
						$('.search-questions-wrapper .results').html('<p>no results</p>');
					}
				},
				error: function(data) {
					console.log('error');
				}
			});
		} else {
			$('.search-questions-wrapper .results').hide();
		}
	});

	$('.delete-answer-avatar').click( function(e) {
		e.preventDefault();

		var that = $(this);

		$.ajax( {
			url: $(this).attr('href'),
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				console.log('success');

				that.closest('.input-group').find('[type="file"]').show();
				that.closest('.input-group').find('.answer-image-wrap').hide();

			},
			error: function(data) {
				console.log('error');
			}
		});
	});


	if($('#generate-stats').length) {
		$('#generate-stats').click( function() {
			if($('#has-stats-already').attr('data') == 'no' && $('#has_stats').is(':checked')) {
				$('#stats-loader').show();
			}
			$(this).closest('form').submit();
		});
	}


	if ($('.select2type').length) {
		$(".select2type").multiSelect();

		if( $('.multi-select-button').text() == '-- Select --') {

			$('.multi-select-button').html('Select Questions')
		}
    }

    $('.show-questions').click( function() {
    	var that = $(this);

    	$.ajax( {
			url: window.location.origin+'/cms/vox/get-questions-count/'+$(this).attr('vox-id'),
			type: 'POST',
			dataType: 'json',
			success: function( data ) {
				that.closest('div').html(data.q_count);
			},
			error: function(data) {
				console.log('error');
			}
		});
    });

    $('.show-respondents').click( function() {
    	var that = $(this);

    	$.ajax( {
			url: window.location.origin+'/cms/vox/get-respondents-count/'+$(this).attr('vox-id'),
			type: 'POST',
			dataType: 'json',
			success: function( data ) {
				that.hide();
				that.closest('div').find('.respondents-shown').html(data.resp_count);
			},
			error: function(data) {
				console.log('error');
			}
		});
    });

    $('.show-reward').click( function() {
    	var that = $(this);

    	$.ajax( {
			url: window.location.origin+'/cms/vox/get-reward/'+$(this).attr('vox-id'),
			type: 'POST',
			dataType: 'json',
			success: function( data ) {
				that.hide();
				that.closest('div').html(data.reward);
			},
			error: function(data) {
				console.log('error');
			}
		});
    });

    $('.show-duration').click( function() {
    	var that = $(this);

    	$.ajax( {
			url: window.location.origin+'/cms/vox/get-duration/'+$(this).attr('vox-id'),
			type: 'POST',
			dataType: 'json',
			success: function( data ) {
				that.hide();
				that.closest('div').html(data.duration);
			},
			error: function(data) {
				console.log('error');
			}
		});
    });	


	var excludeAnswersCheckbox = function() {

		if($('#exclude_answers').is(":checked")) {
			$('.answer-groups-wrapper').show();
		} else {
			$('.answer-groups-wrapper').hide();
		}
	}

	excludeAnswersCheckbox();
	$('#exclude_answers').change(excludeAnswersCheckbox);
	
	if($('#exclude_answers').length) {
		$( "ul.answer-group" ).sortable({
			connectWith: "ul.answer-group",
			update: function( event, ui ) { 
				console.log('update');
				setTimeout( function(){
					
					var groups = [];
					$( "ul.answer-group:not(.with-answers):visible" ).each( function() {
						if($(this).children().length) {
							var answers = [];
							$(this).children().each( function() {
								answers.push($(this).attr('answer'));
							});
							groups.push(answers);
						}
					})

					console.log(groups);
					$('#excluded-answers').val(JSON.stringify(groups));
				}, 0);
			},
		}).disableSelection();
	}

	$('.add-answer-group').click( function() {
		$(this).closest('.groups').find('.answer-group:hidden').first().show();
	});
	
	$('#stay-on-same-page').click( function() {
		$('[name="stay-on-same-page"]').val('1');
		$(this).closest('form').submit();
	});
    
});