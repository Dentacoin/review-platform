var first_test = {};

$(document).ready(function(){

    VoxTest.handleNextQuestion();

    if( $.cookie('first_test') ) {
        $("#first-test-done").modal({backdrop: 'static', keyboard: false});
    }
	
    $('.question-group a.answer').click( function() {
        var group = $(this).closest('.question-group');
        var qid = parseInt(group.attr('data-id'));
        first_test[ qid ] = parseInt( $(this).attr('data-num') );
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

});