var ajax_is_running = false;

$(document).ready(function(){

    $('.preferences-button-anonymous').click( function() {
    	var email = $(this).attr('email');
    	var that = $(this);

    	$.ajax({
            type: 'POST',
            url: 'https://api.dentacoin.com/api/anonymous-email-preferences',
            data: {
                email: email
            },
            dataType: 'json',
            success: function (response) {
            	if(response.data) {
            		for( var i in response.data) {
            			if(i == 'blog') {
            				var value = [];
            				for(var u in response.data[i]) {
            					if(response.data[i][u]['checked']) {
            						value.push(u);
            					}
            				}

            				if(value.length) {
            					arr = value.join(',');
            					that.closest('tr').find('.blog').html(arr);
            				}
            			}
            		}
            	}
            }
        });
    });

});