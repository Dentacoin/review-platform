$(document).ready(function(){

	$('.polls-form .btn-add-answer').click( function() {
		$('.polls-form .questions-pane').each( function() {
			var code = $(this).attr('lang');
			var newinput = $('#input-group-template').clone(true).removeAttr('id')
			newinput.find('textarea.answer-name').attr('name', 'answers-'+code+'[]');
			$(this).find('.answers-list').append(newinput);
		} );
	} );

	$( ".polls-draggable" ).sortable().disableSelection();

	

	$('.poll-date, .poll-question').on('change blur', function() {

        setTimeout( (function() {

    		console.log( $(this).attr('data-qid'), $(this).val() );

            if(ajax_action) {
                return;
            }
            ajax_action = true;
            var urlpart;
            if( $(this).hasClass('poll-question') ) {
            	urlpart = 'question';
            } else {
            	urlpart = 'date';
            }
            

            $.ajax({
                url     : $('#polls-actions').attr('action') + '/change-'+urlpart+'/'+$(this).attr('data-qid'),
                type    : 'POST',
                data 	: {
                	val: $(this).val()
                },
                dataType: 'json',
                success : (function( res ) {
                    ajax_action = false;
                }).bind( this ),
                error : function( data ) {
                    ajax_action = false;
                }
            });

        }).bind(this), 100 );
	});

	$('.polldatepicker').datepicker({
        todayHighlight: true,
        dateFormat: 'yy-mm-dd',
    });

    $('.poll-answers').on('keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            $(this).closest('.questions-pane').find('.btn-add-answer').trigger('click');
            $(this).closest('.answers-list').find('.poll-answers').last().focus();
        }
    });

    function catchPaste(evt, elem, callback) {
      if (navigator.clipboard && navigator.clipboard.readText) {
        // modern approach with Clipboard API
        navigator.clipboard.readText().then(callback);
      } else if (evt.originalEvent && evt.originalEvent.clipboardData) {
        // OriginalEvent is a property from jQuery, normalizing the event object
        callback(evt.originalEvent.clipboardData.getData('text'));
      } else if (evt.clipboardData) {
        // used in some browsers for clipboardData
        callback(evt.clipboardData.getData('text/plain'));
      } else if (window.clipboardData) {
        // Older clipboardData version for Internet Explorer only
        callback(window.clipboardData.getData('Text'));
      } else {
        // Last resort fallback, using a timer
        setTimeout(function() {
          callback(elem.value)
        }, 100);
      }
    }

    $('.poll-answers').first().bind("paste", function(e) {

        catchPaste(e, this, function(clipData) {
            var val = clipData.replace('\n', '<br/>');
            textarea_val = val.split('<br/>');

            console.log(textarea_val.length);
            if(textarea_val.length > 1) {

                for( var i in textarea_val) {
                    $('.polls-form .questions-pane').each( function() {
                        var code = $(this).attr('lang');
                        var newinput = $('#input-group-template').clone(true).removeAttr('id')
                        newinput.find('textarea.answer-name').attr('name', 'answers-'+code+'[]');
                        newinput.find('textarea').val(textarea_val[i]);
                        $(this).find('.answers-list').append(newinput);
                        $('.first-group').remove();
                    } );
                }
            }

        });

    });

    var handleScaleChanges = function() {

        if($('.scale-input').val() ) {
            $('.answers-group, .answers-group-add').hide();
        } else {
            $('.answers-group, .answers-group-add').show();
        }
    }

    $('.scale-input').change(handleScaleChanges);
    handleScaleChanges();
});