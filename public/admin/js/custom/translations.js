$(document).ready(function(){
	console.log('hihih');

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