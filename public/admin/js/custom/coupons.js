$(document).ready(function(){
	var country = $('#countries option:selected' ).text();
	$("#states").children("optgroup[label!='" + country + "']").hide();

	var state = $('#states option:selected' ).text();
	$("#cities").children("optgroup[label!='" + state + "']").hide();

	var city = $('#cities option:selected' ).text();
	$("#districts").children("optgroup[label!='" + city + "']").hide();

	$('#countries').change(function() {
		var country = $('#countries option:selected' ).text();
		$("#states").children("optgroup").hide();
		$("#states").children("optgroup[label='" + country + "']").show();
		$("#states").children("optgroup[label='']").show();
		$("#states").val( $("#states").children("optgroup[label='" + country + "']").children('option').first().val() );

		$("#states").change();
		$("#cities").change();
	});

	$('#states').change(function() {
		var state = $('#states option:selected' ).text();
		$("#cities").children("optgroup").hide();
		$("#cities").children("optgroup[label='" + state + "']").show();
		$("#cities").children("optgroup[label='']").show();
		$("#cities").val( $("#cities").children("optgroup[label='" + state + "']").children('option').first().val() );

		$("#cities").change();
	});

	$('#cities').change(function() {
		var city = $('#cities option:selected' ).text();
		$("#districts").children("optgroup").hide();
		$("#districts").children("optgroup[label='" + city + "']").show();
		$("#districts").children("optgroup[label='']").show();
		if( $("#districts").children("optgroup[label='" + city + "']").children('option').length ) {
			$("#districts").val( $("#districts").children("optgroup[label='" + city + "']").children('option').first().val() );
		} else {
			$("#districts").val('');
		}
	});

	$('.daterange-picker span').each( function() {
		var id = $(this).parent().attr('id');
		var startm = $('#'+id+'_from').val().length ? moment.unix( parseInt($('#'+id+'_from').val()) ) : moment().add(-1, 'month');
		var endm = $('#'+id+'_to').val().length ? moment.unix( parseInt($('#'+id+'_to').val()) ) : moment();
		$(this).html(startm.format('DD-MM-YYYY') + ' - ' + endm.format('DD-MM-YYYY'));
		$('#'+id+'_from').val( startm.format('X') );
		$('#'+id+'_to').val( endm.format('X') );
	});

	$('.daterange-picker').each( function() {
		var id = $(this).attr('id');
		var startm = moment();
		var endm = moment().add(1, 'month');
		$(this).daterangepicker({
		    format: 'DD-MM-YYYY',
		    startDate: startm,
		    endDate: endm,
		    showDropdowns: true,
		    showWeekNumbers: true,
		    timePicker: false,
		    timePickerIncrement: 1,
		    timePicker12Hour: true,
		    ranges: {
		       'Today': [moment(), moment()],
		       'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		       'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		       'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		       'This Month': [moment().startOf('month'), moment().endOf('month')],
		       'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		    },
		    opens: 'right',
		    drops: 'down',
		    buttonClasses: ['btn', 'btn-sm'],
		    applyClass: 'btn-primary',
		    cancelClass: 'btn-default',
		    separator: ' to ',
		    locale: {
		        applyLabel: 'Submit',
		        cancelLabel: 'Cancel',
		        fromLabel: 'From',
		        toLabel: 'To',
		        customRangeLabel: 'Custom',
		        daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
		        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
		        firstDay: 1
		    }
		}, function(start, end, label) {
			var id = $($(this)[0].element[0]).attr('id');
			$('#'+id+'_from').val( start.format('X') );
			$('#'+id+'_to').val( end.format('X') );
		    $('#'+id+' span').html(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
		});
	});
});