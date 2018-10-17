$(document).ready(function(){

	$('.sort-menu a').click( function() {
		if (!$(this).hasClass('active')) {
			$('.sort-menu a').removeClass('active');
			$(this).addClass('active');
		}

		handleFilters();
	} );


	$('#survey-search').on('change keyup', function() {
		handleSearch();
	});

	$('#surveys-categories').on('change', function() {
		handleSearch();
	});

	$('.survey-cats span').click( function() {
		$('#surveys-categories').val( $(this).attr('cat-id') ).trigger('change');
	} );

	var handleFilters = function() {
		if(!$('.sort-menu a.active').length) {
			return;
		}

		$('#surveys-categories').val('all');
		$('#survey-search').val('');

		var sort = $('.sort-menu a.active').attr('sort');
		var wrapper = $('.questions-inner');
		var list = wrapper.children('.another-question');

		if (sort == 'taken') {
			list.hide().attr("found", 0);

			list.each( function() {
				if ($(this).attr('taken')=='1') {
					$(this).show().attr("found", 1);
				}
			});

		} else if (sort == 'featured') {
			list.hide().attr("found", 0);

			list.sort(function(a, b) {
				if( parseInt($(a).attr('published')) > parseInt($(b).attr('published')) ) {
					return -1;
				} else {
					return 1;
				}
			});
			
			list.each( function() {
				if ($(this).attr('featured')=='1') {
					$(this).show().attr("found", 1)
				}
				if ($(this).attr('taken')=='1') {
					$(this).hide().attr("found", 0);
				}
			});

			list.each(function() {
			    wrapper.append(this);
			});

		} else {


			list.show().attr("found", 1);

			if(sort=='newest') {
				list.sort(function(a, b) {
					if( parseInt($(a).attr('published')) > parseInt($(b).attr('published')) ) {
						return -1;
					} else {
						return 1;
					}
				});
			} else if(sort=='popular') {
				list.sort(function(a, b) {
					if( parseInt($(a).attr('popular')) > parseInt($(b).attr('popular')) ) {
						return -1;
					} else {
						return 1;
					}
				});
			} else if(sort=='reward') {
				list.sort(function(a, b) {
					if( parseInt($(a).attr('dcn')) > parseInt($(b).attr('dcn')) ) {
						return -1;
					} else {
						return 1;
					}
				});
			} else if(sort=='duration') {
				list.sort(function(a, b) {
					if( parseInt($(a).attr('duration')) > parseInt($(b).attr('duration')) ) {
						return -1;
					} else {
						return 1;
					}
				});
			}

			list.each( function() {
				if ($(this).attr('taken')=='1') {
					$(this).hide().attr("found", 0);
				}
			});

			list.each(function() {
			    wrapper.append(this);
			});
		}
		
		$('#survey-not-found').hide();
		setupPagination();
	}


	var handleSearch = function() {
		$('.sort-menu a.active').removeClass('active');

		$('.another-question').show().attr("found", 1);;

		if ($('#survey-search').val().length > 3) {
			$('.another-question').each( function() {
				if( $(this).find('.survey-title').text().toLowerCase().indexOf($('#survey-search').val().toLowerCase()) == -1) {
					$(this).hide().attr("found", 0);
				}
			});
		}

		if( $('#surveys-categories').val() != 'all' ) {
			$('.another-question').each( function() {
				if(!$(this).find('.survey-cat[cat-id="'+ $('#surveys-categories').val() +'"]').length) {
					$(this).hide().attr("found", 0);
				}
			});
		}

		if ( !$('.another-question:visible').length ) {
			$('#survey-not-found').show();
		} else {
			$('#survey-not-found').hide();
		}
	
		setupPagination();	
	}

	var setupPagination = function() {
		$('#survey-more').hide();
		var total = $('.another-question:visible').length;
		if(total>5) {
			var i=0;
			$('.another-question:visible').each( function() {
				i++;
				if(i>5) {
					$(this).hide();
				}
			} );
			$('#survey-more').show();
		}
	}

	$('#survey-more').click( function() {
		var i=0;
		$('.another-question[found="1"]:not(:visible)').each( function() {
			i++;
			if(i<=5) {
				$(this).show();
			}
		} );

		if( !$('.another-question[found="1"]:not(:visible)').length ) {
			$(this).hide();
		}

	} )

});