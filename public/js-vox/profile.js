$(document).ready(function(){

	$('#idea-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;
        $('#idea-form alert').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('#idea').val('');
                	$('#idea-success').show();
                } else {
                	$('#idea-error').show();
                }
                ajax_is_running = false;
            }, "json"
        );

	} );


    //Invites

    $('.btn-group-justified label').click( function() {
        var id = $(this).attr('for');
        $('.option-div').hide();
        $('#option-mode').show();
        $('#widget-preview').show();
        $('#'+id).show();
        $('.btn-group-justified .btn').removeClass('btn-primary');
        $(this).addClass('btn-primary');
    } );


    if( $('#invite-patient-form').length ) {

        $('#invite-patient-form').submit( function(e) {
            e.preventDefault();

            $('#invite-alert').hide();

            if(ajax_is_running) {
                return;
            }

            ajax_is_running = true;

            $('#invite-alert').hide().removeClass('alert-warning').removeClass('alert-success');

            $.post( 
                $(this).attr('action'), 
                $(this).serialize() , 
                function( data ) {
                    if(data.success) {
                        $('#invite-email').val('');
                        $('#invite-name').val('').focus();
                        $('#invite-alert').show().addClass('alert-success').html(data.message);
                    } else {
                        $('#invite-alert').show().addClass('alert-warning').html(data.message);                    
                    }
                    ajax_is_running = false;
                }, "json"
            );

            return false;
        } );

        $('#share-contacts-form').submit( function(e) {
            e.preventDefault();
            if(ajax_is_running) {
                return;
            }
            ajax_is_running = true;

            $.post( 
                $(this).attr('action'), 
                $(this).serialize() , 
                function( data ) {
                    if(data.success) {
                        $('#contacts-results').hide();
                        $('#contacts-alert').show().addClass('alert-success').html(data.message);
                    } else {
                        $('#contacts-alert').show().addClass('alert-warning').html(data.message);                    
                    }
                    ajax_is_running = false;
                }, "json"
            );

            return false;
        } );

        hello.on('auth.login', function(auth) {

            // Call user information, for the given network
            hello(auth.network).api('me').then(function(r) {
                console.log( auth.network );
                console.log( r );
            });
        });

        $('.btn-share-contacts').click( function() {
            $('#contacts-alert').hide();
            $('#contacts-results').hide();
            $('#contacts-error').hide();
            $('#contacts-results-empty').hide();
            var network = $(this).attr('data-netowrk');
            // login
            hello(network).login({scope:'friends'}).then(function(auth) {
                // Get the friends
                // using path, me/friends or me/contacts
                hello(network).api('me/'+(network=='yahoo' ? 'friends' : 'contacts'), {limit:1000}).then(function responseHandler(r) {
                    console.log(r);
                    var found = false;
                    $('#contacts-results-list').html('');
                    for(var i in r.data) {
                        if(r.data[i].email && r.data[i].email.indexOf('@')!=-1) {
                            $('#contacts-results-list').append('<label for="contact-'+i+'" class="form-control"><input id="contact-'+i+'" type="checkbox" name="contacts[]" value="'+(r.data[i].name ? r.data[i].name+'|' : '')+r.data[i].email+'" /> '+(r.data[i].name ? r.data[i].name+' ('+r.data[i].email+')' : r.data[i].email)+'</label>');
                            found = true;
                        }
                    }
                    if(!found) {
                        $('#contacts-results-empty').show();
                    } else {
                        $('#contacts-results').show();                        
                    }
                });
            }, function() {
                if(!auth||auth.error){
                    $('#contacts-error').show();
                    console.log("Signin aborted");
                    return;
                }
            });
            
        } )


        hello.init({
            windows: 'f5c6f6f7-aed0-4477-8ad2-d6b264b0a491',
            google: '313352423951-bl64tutb9f7fdl2bjljgref1lriujinp.apps.googleusercontent.com',
            yahoo: 'dj0yJmk9YzZhMlhjcm1WWWR0JmQ9WVdrOWVVdGhSM05OTkdzbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD01ZA--'
        }, {
            redirect_uri: socials_redirect_url,
            scope: "basic,friends",
            oauth_proxy: 'https://auth-server.herokuapp.com/proxy'
        });

        $('#search-contacts').on( 'change keyup', function() {
            var s = $(this).val().toLowerCase();
            if(s.length>3) {
                $('#contacts-results-list label').hide();
                $('#contacts-results-list label').each( function() {
                    if( $(this).find('input').first().val().toLowerCase().indexOf( s ) !=-1 ) {
                        $(this).show();
                    }
                } );

            } else {
                $('#contacts-results-list label').show();
                
            }
        } );

    }

});