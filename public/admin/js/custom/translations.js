var searchTO;

$(document).ready(function(){

	if( typeof(should_reload)!='undefined' ) {
		setTimeout( function() {
			window.location.href = window.location.href.split('?')[0] + '?reloaded=1';			
		}, 1000 );
	}

	var goSearch = function() {
		var text = $(this).val();
		if(text.length>3) {
			$('tr').show();
			$('tr.info').hide();
			$('tr:not(.info)').each( function() {
				//console.log( $(this).html().toLowerCase() );
				if( $(this).html().toLowerCase().indexOf(text.toLowerCase())!=-1 ) {
					console.log('ok');
					$(this).show();
				} else {
					console.log('not');
					$(this).hide();
				}
			} );
		} else {
			$('tr:not(.info)').show();
			$('tr').hide();
			$('tr.info').show();
		}

		$('.bnt-tr').show();
	}


	$('#search-translations').on('change keyup', function() {
		if( searchTO ) {
			clearTimeout(searchTO);
		}
		searchTO = setTimeout(goSearch.bind(this), 1000);
	});

	$('#translations-change').submit( function(e) {
		e.preventDefault();
		window.location.href = '/cms/'+current_page+'/'+current_subpage+'/'+$('#translate-from').val()+'/'+$('#translate-to').val()
	});

	if($('#translations-save table tr.info').length) {

		var currow = null;
		var trans_all = 0;
		var trans_done = 0;
		$('#translations-save table tr').each( function() {
			if($(this).hasClass('bnt-tr')) {
				;
			} else if($(this).hasClass('info')) {
				if(currow) {
					currow.find('td').append(' - '+trans_done+' / '+trans_all);
				}

				currow = $(this);
				currow.css('cursor', 'pointer');
				trans_all = 0;
				trans_done = 0;
				currow.click(toggleTransRow);
			} else {
				var ta = $(this).find('textarea');
				if(ta.length) {
					var trans = $(this).find('textarea').val();	
					if(trans.length) {
						trans_done++;
					}
				}
					
				trans_all++;
				$(this).hide();
			}
		} );
		
		currow.find('td').append(' - '+trans_done+' / '+trans_all);
	}
});

function toggleTransRow() {
	var nextobj = $(this).next();
	while(!(nextobj.hasClass('info') || nextobj.hasClass('bnt-tr'))) {
		nextobj.toggle();
		nextobj = nextobj.next();
	}
}