$(document).ready(function(){


	$('#files-input').change( function(е) {
		var total_pics = 0;
		var uploaded_pics = 0;
		var xhrs = [];
		
		for(var i=0;i<this.files.length;i++) {
			total_pics++;
			var file = this.files[i];
			xhrs[total_pics] = new XMLHttpRequest();

			// progress bar
			$('#files-progress').show();
			
			// file received/failed
			xhrs[total_pics].onreadystatechange = (function(e) {
				if (xhrs[this.pic_id].readyState == 4) {
					uploaded_pics++;
					if(xhrs[this.pic_id].status == 200) {
						var res = JSON.parse(xhrs[this.pic_id].responseText);
						if(res.success) {
							$('#files-progress-bar').css('width', ((uploaded_pics/total_pics)*90)+'%' );
						} else {
							$('#files-progress-status').append( 'Problem uploading picture '+this.pic_id +'<br/>');
						}
					} else {
						$('#files-progress-status').append( 'Problem uploading picture '+this.pic_id  +'<br/>');
					}

					if(uploaded_pics==total_pics) {
						window.location.reload();
					}
				}
			}).bind({ pic_id: total_pics });

			// start upload
			xhrs[total_pics].open("POST", $(this).closest('form').attr('action')+'?_token=' + $(this).closest('form').find("input[name='_token']").val(), true); //
			xhrs[total_pics].setRequestHeader("ajax-upload", 1);
			var formData = new FormData();
			formData.append("thefile", file);
			xhrs[total_pics].send(formData);
		}
		
		$('#files-progress-bar').css('width', '10%' );
	});


	$('form').off('submit').submit( function(e) {
    	e.preventDefault();
    	$('#error-message').hide();

    	var form_data = getFormData( $(this) );
    	for(var code in langs) {
    		form_data['content-'+code] = [];
    		$('#nav-tab-'+code).find('.content-blocks .panel').each( function() {
    			if($(this).hasClass('template-add-html')) {
    				
    				form_data['content-'+code].push({
    					type: 'html',
    					content: CKEDITOR.instances[ $(this).find('.page-content-div').attr('id') ].getData(),
    				});
    			}    			
    		} )
    	}

		if(ajax_action) {
			return;
		}
		ajax_action = true;


		$.ajax({
            url     : $(this).attr('action'),
            type    : $(this).attr('method'),
            data    : form_data,
            dataType: 'json',
            success : function( res ) {
            	ajax_action = false;
				if(res && res.success) {
					window.location.href = res.href;
				} else {
					$('#error-message').html('').show();

					for(var i in res.messages) {
						$('#error-message').append(res.messages[i]+'<br/>');
					}
				}
			},
            error : function( data ) {
				ajax_action = false;
				$('#error-message').html('Network Error').show();
			}
		});
    });

});