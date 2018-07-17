var first_test = {};

$(document).ready(function(){

    VoxTest.handleNextQuestion();

    if( $.cookie('first_test') ) {
        $("#first-test-done").modal({backdrop: 'static', keyboard: false});
    }
	
    $('.question-group a.answer, .question-group a.next-answer').click( function() {
        var group = $(this).closest('.question-group');
        var qid = group.attr('data-id');
        var answer = null;

        if( group.hasClass('birthyear-question') ) {
            if (!( $('#birthyear-answer').val().length && parseInt( $('#birthyear-answer').val() ) > 1900 && parseInt( $('#birthyear-answer').val() ) < 2000 )) {
                $('.answer-error').show().insertAfter($(this));
                return;
            }
            answer = $('#birthyear-answer').val();

        } else if (group.hasClass('location-question')) {

            if (!($('select').length == $('select option:selected').length && $('select option:selected').val())) {
                $('.answer-error').show().insertAfter($(this));
                return;
            }
            answer = $('.country-select option:selected').val() + ',' + $('.city-select option:selected').val();
        } else {
            answer = $(this).attr('data-num');
        }

        first_test[ qid ] = answer;
        group.hide();
        group.next().show();

        if( group.next().hasClass('question-done') ) {
            $.cookie('first_test', first_test, { expires: 1 });
            $('.question-hints').hide();
            $("#first-test-done").modal({backdrop: 'static', keyboard: false});

        } else {
            vox.current++;            
        }

        VoxTest.handleNextQuestion();
        
    } );

    $('.country-select').change( function() {
        var city_select = $(this).closest('.answers').find('.city-select').first();
        city_select.attr('disabled', 'disabled');
        $.ajax( {
            url: '/cities/' + $(this).val(),
            type: 'GET',
            dataType: 'json',
            success: function( data ) {
                city_select.attr('disabled', false)
                .find('option')
                .remove();
                for(var i in data.cities) {
                    city_select.append('<option value="'+i+'" '+(fb_city_id && fb_city_id==data.cities[i] ? 'selected="selected"' : '' )+'>'+data.cities[i]+'</option>');
                }
                //city_select
                //$('#modal-message .modal-body').html(data);
            }
        });
    } );

});