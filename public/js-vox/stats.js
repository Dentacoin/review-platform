$(document).ready(function(){

    //All surveys
    $('.stats-cats a.cat').click( function() {
        $(this).toggleClass('active');
        $(this).next().toggleClass('active');
    });

    $('#survey-search').on('keypress keyup', function(e) {
        if ($('#survey-search').val().length > 3) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) { 
                $(this).closest('form').submit();
            }
        }
    }); 

    if ($('.normal-stat').length < 1) {
        $('#survey-not-found').show();
    }
});