var searchTO = null;
var searchNameTO = null;
var searchCityTO = null;
var autocomplete;

jQuery(document).ready(function($) {

	var dentistLocationSuggestions = function() {
		var query = $('#search-dentist-city').val();
		if($('#search-country-id').val()) {
			var options = { 
				input: query,
				types: ['(cities)'],
				componentRestrictions: { country:  $('#search-dentist-country').attr('country-code') },
				//types: ['(cities)', 'country'],
			};
		} else {
			var options = { 
				input: query,
				types: ['(cities)'],
				//types: ['(cities)', 'country'],
			};
		}
		autocomplete.getPlacePredictions(options, displayCitySuggestions);
	}

	var dentistNameSuggestions = function(forForm=false) {
		$('.dentists-names-results').hide();
		$('.dentists-names-results .dentists-results').hide();
		$('.dentists-names-results .dentists-results .list').html('');
		$('.dentists-names-results .dentists-results .info').remove();

		let formData = null;
		if(forForm) {
			formData = new FormData($('.search-form')[0]);
		} else {
			formData = {
				dentist_name: $('#search-dentist-name').val(),
				dentist_country_id: $('#search-country-id').val(),
				dentist_country_name: $('#search-dentist-country').val(),
				dentist_city: $('#search-dentist-city').val(),
				is_partner: $('#partner').is(':checked') ? 1 : 0,
			}
		}

		$.ajax( {
			url: '/search-dentists',
			type: 'POST',
			data: formData,
			dataType: 'json',
	        cache: false,
	        contentType: forForm ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
	        processData: forForm ? false : true,
			success: function( data ) {
				
				if(data.success && data.redirect) {
					console.log(data.redirect);
					window.location.href = data.redirect;
				}
				
				$('.dentists-names-results').show();
				$('.dentists-names-results .dentists-results').show();
				$('.dentists-names-results .dentists-results .list').html('');
				$('.dentists-names-results .dentists-results .info').remove();

				if(data.dentists) {
					if(data.alert) {
						$('.dentists-results').prepend('<p class="info">'+data.alert+'</p>');
					}

					var dentists = data.dentists;

					for(var i in dentists) {
						var u_name = dentists[i].status == 'dentist_no_email' ? dentists[i].team_clinic_name : dentists[i].name;
						var attr_name = dentists[i].status == 'dentist_no_email' ? dentists[i].team_clinic_name : dentists[i].pure_name;

						var is_partner = dentists[i].is_partner ? '\
							<div class="result-partner-dentist">\
								<div class="result-partner-dentist-wrapper">\
									<img src="'+images_path+'/mini-logo-white.svg">\
									<span>Dentacoin</span> Partner\
								</div>\
							</div>\
						' : '';

						var isTeamDentist = dentists[i].status == 'dentist_no_email' ? '\
							<div class="team-dentist">\
								<div class="flex flex-mobile flex-center">\
									<div class="result-image-dentist">\
										<img src="'+dentists[i].team_clinic_avatar+'"/>\
									</div>\
									<div class="result-name-dentist">\
										<p>'+dentists[i].name+'</p>\
									</div>\
								</div>\
							</div>\
						' : '';

						$('.dentists-names-results .dentists-results .list').append('\
							<a class="dentist-button '+(dentists[i].status == 'dentist_no_email' ? 'with-team-member' : '')+'" dentist-id="'+dentists[i].id+'" dentist-name="'+attr_name+'" href="'+dentists[i].link+'">\
								<div class="flex flex-mobile">\
									<div class="result-image-dentist">\
										<img src="'+(dentists[i].team_clinic_avatar ? dentists[i].team_clinic_avatar : dentists[i].avatar)+'"/>\
									</div>\
									<div class="result-name-dentist">\
										<p>'+u_name+'</p>\
										<span>'+(dentists[i].team_clinic_location ? dentists[i].team_clinic_location : dentists[i].location)+'</span>\
									</div>\
									'+is_partner+'\
								</div>\
								'+isTeamDentist+'\
							</a>\
						');
					}
				} else if(data.alert) {
					$('.dentists-results').prepend('<p class="info error">'+data.alert+'</p>');
				}
			},
			error: function(data) {
				console.log('ERROR SF')
			}
		});
	}

	$('.search-form').submit( function(e) {
		e.preventDefault();

		dentistNameSuggestions(true);

		// if(ajax_is_running) {
		// 	return;
		// }

		// ajax_is_running = true;

		// let that = $(this);

		// $.post( 
		// 	that.attr('action'), 
		// 	that.serialize() , 
		// 	function( data ) {
		// 		if(data.success) {
		// 		} else {
		// 			console.log('error');
		// 		}
		// 		ajax_is_running = false;
		// 	}, "json"
		// );
	});

	$('#search-dentist-name').on( 'keyup focus', function(e) {

		if(!$('#partner').is(':checked')) {
			$('#partner').closest('label').addClass('jump-it');
		}

		var keyCode = e.keyCode || e.which;

		if (keyCode === 40 || keyCode === 38) { //Down / Up
			var activeLink = $('.dentists-names-results .dentist-button.active');
			if(activeLink.length) {
				activeLink.removeClass('active');
				if( keyCode === 40 ) { // Down
					// console.log('down');
					if( activeLink.next().length ) {
						activeLink.next().addClass('active');
					} else {
						$('.dentists-names-results .dentist-button').first().addClass('active');
					}
					
					activeLink = $('.dentists-names-results .dentist-button.active');
					if(activeLink.prevAll().length) {

						var prevActiveElementsHeight = 0;

						activeLink.prevAll().each( function() {
							prevActiveElementsHeight+=$(this).outerHeight();
						});

						prevActiveElementsHeight+=activeLink.outerHeight();

						if(prevActiveElementsHeight > $('.dentists-names-results').outerHeight()) {

							$('.dentists-names-results').animate({
								scrollTop: $('.dentists-names-results').scrollTop() + activeLink.outerHeight()
							}, 100);
						}
					} else {
						$('.dentists-names-results').animate({
							scrollTop: 0
						}, 100);
					}
				} else { // UP
					if( activeLink.prev().length ) {
						activeLink.prev().addClass('active');
					} else {
						$('.dentists-names-results .dentist-button').last().addClass('active');
					}
					
					activeLink = $('.dentists-names-results .dentist-button.active');
					if(activeLink.nextAll().length) {

						var nextActiveElementsHeight = 0;

						activeLink.nextAll().each( function() {
							nextActiveElementsHeight+=$(this).outerHeight();
						});

						nextActiveElementsHeight+=activeLink.outerHeight();

						if(nextActiveElementsHeight > $('.dentists-names-results').outerHeight()) {

							$('.dentists-names-results').animate({
								scrollTop: $('.dentists-names-results').scrollTop() - activeLink.outerHeight()
							}, 100);
						}
					} else {
						var allDentistButtonsHeight = 0;

						$('.dentists-names-results .dentist-button').each( function() {
							allDentistButtonsHeight+= $(this).outerHeight()
						});

						$('.dentists-names-results').animate({
							scrollTop: allDentistButtonsHeight
						}, 100);
					}
				}
			} else {
				if( $('.dentists-names-results .dentist-button').length ) {
					if (keyCode === 40) { // Down
						$('.dentists-names-results .dentist-button').first().addClass('active');
					} else {
						$('.dentists-names-results .dentist-button').last().addClass('active');            				
					}
				}
			}
		} else if (keyCode === 13) { //enter
			if( $('.dentists-names-results .dentist-button.active').length ) {
				window.location.href = $('.dentists-names-results .dentist-button.active').first().attr('href');
			} else {
				//submit form
			}
			//  else if( $('.dentists-names-results .dentist-button').length ) {
			// 	if ($('.dentists-names-results .dentist-button').length > 1) {
			// 		window.location.href = lang + '/dentists/' + encodeURIComponent( $(this).val() ) + '/all-results';
			// 	} else {
			// 		window.location.href = $('.dentists-names-results .dentist-button').first().attr('href');
			// 	}
			// }
		} else {
			if( $(this).val().length > 2 ) {
				$('.dentists-names-results').show();
				if(searchNameTO) {
					clearTimeout(searchNameTO);
				}
				searchNameTO = setTimeout(dentistNameSuggestions, 300);
			} else {
				// $('#search-country-id').val('');
				$('.dentists-names-results').hide();
			}
		}
	});
	
	$('#search-dentist-country').on('keyup focus', function(e) {

		$('.dentists-countries-results').show();
		$('.letters-country-section').show();
		$('.dentists-countries-results .info').hide();
		
		if($(this).val()) {

			var searched_country = $(this);
			$('.letters-country-section .country').each( function() {

				if (
					$(this).attr('country-name').toLowerCase().indexOf(searched_country.val().toLowerCase()) >= 0 
					|| ( typeof $(this).attr('country-second-name') !== 'undefined' && $(this).attr('country-second-name').toLowerCase().indexOf(searched_country.val().toLowerCase()) >= 0 )
				) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});

			$('.letters-country-section').each( function() {
				
				if($(this).find('.flex').children(':visible').length == 0) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});

			if($('.dentists-countries-results-wrapper').children('.letters-country-section:visible').length == 0) {
				$('.dentists-countries-results .info').show();
			} else {
				$('.dentists-countries-results .info').hide();
			}
		} else {
			$('.letters-country-section').show();
			$('.letters-country-section .country').show();
			$('#search-country-id').val('');
		}
	});

	var displayCitySuggestions = function(predictions, status) {
		$('.dentists-cities-results .info').hide();
		$('.dentists-cities-results-wrapper .locations-results').html('').show();
		if (status != google.maps.places.PlacesServiceStatus.OK) {
			if(status == 'ZERO_RESULTS') {
				$('.dentists-cities-results .info').show();
			}
			console.log(status);
			return;
		}

		predictions.forEach(function(prediction) {

			var href = prediction.description;
			href = href.replace(/\s+/g, '-').toLowerCase();
			href = href.replace(/\,/g, '');

			var city = prediction.description.toLowerCase();
			// var city = prediction.description.toLowerCase().latinise();
			if($('#search-country-id').val()) {
				if(city.split(',').length > 2) {
					city = city.split(',')[0]+', '+city.split(',')[1];
				} else {
					city = city.split(',')[0];
				}
			}

			// if(!city.isLatin()) {
			// 	var lettersToReplace = [];
			// 	for( var i in city) {
			// 		if (fromLatinLetters[city[i]]) {
			// 			lettersToReplace.push(city[i]);
			// 		}
			// 	}
			// }
			
			city = city.replace($('#search-dentist-city').val().toLowerCase(), '<span>'+$('#search-dentist-city').val().toLowerCase()+'</span>');

			if(prediction.description.split(',').length > 2) {
				var city_name = prediction.description.split(',')[0]+', '+prediction.description.split(',')[1];
			} else {
				var city_name = prediction.description.split(',')[0];
			}
			
			$('.dentists-cities-results-wrapper .locations-results').append('\
				<div class="result-city-wrapper">\
					<a class="city-button" href="javascript:;" city-name="'+city_name+'">'+city+'</a>\
				</div>\
			');

			$('.city-button').click( function() {
				$('#search-dentist-city').val($(this).attr('city-name'));
				$('.dentists-cities-results').hide();
			});
		});

		$('.dentists-cities-results-wrapper .locations-results').show();
	};

	prepareMapFunction( function() {
		$('#search-dentist-city').on('keyup focus', function(e) {
			if( $(this).val() ) {
				$('.dentists-cities-results').show();
				if(searchCityTO) {
					clearTimeout(searchCityTO);
				}
				searchCityTO = setTimeout(dentistLocationSuggestions, 300);
			}
		});

		autocomplete = new google.maps.places.AutocompleteService();
	});

	$('body').click( function(e) {
		if($('.dentists-countries-results').is(':visible')) {
			if($(e.target).closest('.dentists-countries-results').length || $(e.target).attr('id') == 'search-dentist-country') {
			} else {
				$('.dentists-countries-results').hide();
			}
		}
		if($('.dentists-cities-results').is(':visible')) {
			if($(e.target).closest('.dentists-cities-results').length || $(e.target).attr('id') == 'search-dentist-city') {
			} else {
				$('.dentists-cities-results').hide();
			}
		}
		if($('.dentists-names-results').is(':visible')) {
			if($(e.target).closest('.dentists-names-results').length || $(e.target).attr('id') == 'search-dentist-name') {
			} else {
				$('.dentists-names-results').hide();
			}
		}
	});

	$('.country-button').click( function() {
		$('#search-dentist-country').val($(this).attr('country-name'));
		$('#search-dentist-country').attr('country-code', $(this).attr('country-code'));
		$('#search-country-id').val($(this).attr('country-id'));
		$('.dentists-countries-results').hide();
	});

});

var Latinise={};
Latinise.latin_map={"Á":"A",
"Ă":"A",
"Ắ":"A",
"Ặ":"A",
"Ằ":"A",
"Ẳ":"A",
"Ẵ":"A",
"Ǎ":"A",
"Â":"A",
"Ấ":"A",
"Ậ":"A",
"Ầ":"A",
"Ẩ":"A",
"Ẫ":"A",
"Ä":"A",
"Ǟ":"A",
"Ȧ":"A",
"Ǡ":"A",
"Ạ":"A",
"Ȁ":"A",
"À":"A",
"Ả":"A",
"Ȃ":"A",
"Ā":"A",
"Ą":"A",
"Å":"A",
"Ǻ":"A",
"Ḁ":"A",
"Ⱥ":"A",
"Ã":"A",
"Ꜳ":"AA",
"Æ":"AE",
"Ǽ":"AE",
"Ǣ":"AE",
"Ꜵ":"AO",
"Ꜷ":"AU",
"Ꜹ":"AV",
"Ꜻ":"AV",
"Ꜽ":"AY",
"Ḃ":"B",
"Ḅ":"B",
"Ɓ":"B",
"Ḇ":"B",
"Ƀ":"B",
"Ƃ":"B",
"Ć":"C",
"Č":"C",
"Ç":"C",
"Ḉ":"C",
"Ĉ":"C",
"Ċ":"C",
"Ƈ":"C",
"Ȼ":"C",
"Ď":"D",
"Ḑ":"D",
"Ḓ":"D",
"Ḋ":"D",
"Ḍ":"D",
"Ɗ":"D",
"Ḏ":"D",
"ǲ":"D",
"ǅ":"D",
"Đ":"D",
"Ƌ":"D",
"Ǳ":"DZ",
"Ǆ":"DZ",
"É":"E",
"Ĕ":"E",
"Ě":"E",
"Ȩ":"E",
"Ḝ":"E",
"Ê":"E",
"Ế":"E",
"Ệ":"E",
"Ề":"E",
"Ể":"E",
"Ễ":"E",
"Ḙ":"E",
"Ë":"E",
"Ė":"E",
"Ẹ":"E",
"Ȅ":"E",
"È":"E",
"Ẻ":"E",
"Ȇ":"E",
"Ē":"E",
"Ḗ":"E",
"Ḕ":"E",
"Ę":"E",
"Ɇ":"E",
"Ẽ":"E",
"Ḛ":"E",
"Ꝫ":"ET",
"Ḟ":"F",
"Ƒ":"F",
"Ǵ":"G",
"Ğ":"G",
"Ǧ":"G",
"Ģ":"G",
"Ĝ":"G",
"Ġ":"G",
"Ɠ":"G",
"Ḡ":"G",
"Ǥ":"G",
"Ḫ":"H",
"Ȟ":"H",
"Ḩ":"H",
"Ĥ":"H",
"Ⱨ":"H",
"Ḧ":"H",
"Ḣ":"H",
"Ḥ":"H",
"Ħ":"H",
"Í":"I",
"Ĭ":"I",
"Ǐ":"I",
"Î":"I",
"Ï":"I",
"Ḯ":"I",
"İ":"I",
"Ị":"I",
"Ȉ":"I",
"Ì":"I",
"Ỉ":"I",
"Ȋ":"I",
"Ī":"I",
"Į":"I",
"Ɨ":"I",
"Ĩ":"I",
"Ḭ":"I",
"Ꝺ":"D",
"Ꝼ":"F",
"Ᵹ":"G",
"Ꞃ":"R",
"Ꞅ":"S",
"Ꞇ":"T",
"Ꝭ":"IS",
"Ĵ":"J",
"Ɉ":"J",
"Ḱ":"K",
"Ǩ":"K",
"Ķ":"K",
"Ⱪ":"K",
"Ꝃ":"K",
"Ḳ":"K",
"Ƙ":"K",
"Ḵ":"K",
"Ꝁ":"K",
"Ꝅ":"K",
"Ĺ":"L",
"Ƚ":"L",
"Ľ":"L",
"Ļ":"L",
"Ḽ":"L",
"Ḷ":"L",
"Ḹ":"L",
"Ⱡ":"L",
"Ꝉ":"L",
"Ḻ":"L",
"Ŀ":"L",
"Ɫ":"L",
"ǈ":"L",
"Ł":"L",
"Ǉ":"LJ",
"Ḿ":"M",
"Ṁ":"M",
"Ṃ":"M",
"Ɱ":"M",
"Ń":"N",
"Ň":"N",
"Ņ":"N",
"Ṋ":"N",
"Ṅ":"N",
"Ṇ":"N",
"Ǹ":"N",
"Ɲ":"N",
"Ṉ":"N",
"Ƞ":"N",
"ǋ":"N",
"Ñ":"N",
"Ǌ":"NJ",
"Ó":"O",
"Ŏ":"O",
"Ǒ":"O",
"Ô":"O",
"Ố":"O",
"Ộ":"O",
"Ồ":"O",
"Ổ":"O",
"Ỗ":"O",
"Ö":"O",
"Ȫ":"O",
"Ȯ":"O",
"Ȱ":"O",
"Ọ":"O",
"Ő":"O",
"Ȍ":"O",
"Ò":"O",
"Ỏ":"O",
"Ơ":"O",
"Ớ":"O",
"Ợ":"O",
"Ờ":"O",
"Ở":"O",
"Ỡ":"O",
"Ȏ":"O",
"Ꝋ":"O",
"Ꝍ":"O",
"Ō":"O",
"Ṓ":"O",
"Ṑ":"O",
"Ɵ":"O",
"Ǫ":"O",
"Ǭ":"O",
"Ø":"O",
"Ǿ":"O",
"Õ":"O",
"Ṍ":"O",
"Ṏ":"O",
"Ȭ":"O",
"Ƣ":"OI",
"Ꝏ":"OO",
"Ɛ":"E",
"Ɔ":"O",
"Ȣ":"OU",
"Ṕ":"P",
"Ṗ":"P",
"Ꝓ":"P",
"Ƥ":"P",
"Ꝕ":"P",
"Ᵽ":"P",
"Ꝑ":"P",
"Ꝙ":"Q",
"Ꝗ":"Q",
"Ŕ":"R",
"Ř":"R",
"Ŗ":"R",
"Ṙ":"R",
"Ṛ":"R",
"Ṝ":"R",
"Ȑ":"R",
"Ȓ":"R",
"Ṟ":"R",
"Ɍ":"R",
"Ɽ":"R",
"Ꜿ":"C",
"Ǝ":"E",
"Ś":"S",
"Ṥ":"S",
"Š":"S",
"Ṧ":"S",
"Ş":"S",
"Ŝ":"S",
"Ș":"S",
"Ṡ":"S",
"Ṣ":"S",
"Ṩ":"S",
"Ť":"T",
"Ţ":"T",
"Ṱ":"T",
"Ț":"T",
"Ⱦ":"T",
"Ṫ":"T",
"Ṭ":"T",
"Ƭ":"T",
"Ṯ":"T",
"Ʈ":"T",
"Ŧ":"T",
"Ɐ":"A",
"Ꞁ":"L",
"Ɯ":"M",
"Ʌ":"V",
"Ꜩ":"TZ",
"Ú":"U",
"Ŭ":"U",
"Ǔ":"U",
"Û":"U",
"Ṷ":"U",
"Ü":"U",
"Ǘ":"U",
"Ǚ":"U",
"Ǜ":"U",
"Ǖ":"U",
"Ṳ":"U",
"Ụ":"U",
"Ű":"U",
"Ȕ":"U",
"Ù":"U",
"Ủ":"U",
"Ư":"U",
"Ứ":"U",
"Ự":"U",
"Ừ":"U",
"Ử":"U",
"Ữ":"U",
"Ȗ":"U",
"Ū":"U",
"Ṻ":"U",
"Ų":"U",
"Ů":"U",
"Ũ":"U",
"Ṹ":"U",
"Ṵ":"U",
"Ꝟ":"V",
"Ṿ":"V",
"Ʋ":"V",
"Ṽ":"V",
"Ꝡ":"VY",
"Ẃ":"W",
"Ŵ":"W",
"Ẅ":"W",
"Ẇ":"W",
"Ẉ":"W",
"Ẁ":"W",
"Ⱳ":"W",
"Ẍ":"X",
"Ẋ":"X",
"Ý":"Y",
"Ŷ":"Y",
"Ÿ":"Y",
"Ẏ":"Y",
"Ỵ":"Y",
"Ỳ":"Y",
"Ƴ":"Y",
"Ỷ":"Y",
"Ỿ":"Y",
"Ȳ":"Y",
"Ɏ":"Y",
"Ỹ":"Y",
"Ź":"Z",
"Ž":"Z",
"Ẑ":"Z",
"Ⱬ":"Z",
"Ż":"Z",
"Ẓ":"Z",
"Ȥ":"Z",
"Ẕ":"Z",
"Ƶ":"Z",
"Ĳ":"IJ",
"Œ":"OE",
"ᴀ":"A",
"ᴁ":"AE",
"ʙ":"B",
"ᴃ":"B",
"ᴄ":"C",
"ᴅ":"D",
"ᴇ":"E",
"ꜰ":"F",
"ɢ":"G",
"ʛ":"G",
"ʜ":"H",
"ɪ":"I",
"ʁ":"R",
"ᴊ":"J",
"ᴋ":"K",
"ʟ":"L",
"ᴌ":"L",
"ᴍ":"M",
"ɴ":"N",
"ᴏ":"O",
"ɶ":"OE",
"ᴐ":"O",
"ᴕ":"OU",
"ᴘ":"P",
"ʀ":"R",
"ᴎ":"N",
"ᴙ":"R",
"ꜱ":"S",
"ᴛ":"T",
"ⱻ":"E",
"ᴚ":"R",
"ᴜ":"U",
"ᴠ":"V",
"ᴡ":"W",
"ʏ":"Y",
"ᴢ":"Z",
"á":"a",
"ă":"a",
"ắ":"a",
"ặ":"a",
"ằ":"a",
"ẳ":"a",
"ẵ":"a",
"ǎ":"a",
"â":"a",
"ấ":"a",
"ậ":"a",
"ầ":"a",
"ẩ":"a",
"ẫ":"a",
"ä":"a",
"ǟ":"a",
"ȧ":"a",
"ǡ":"a",
"ạ":"a",
"ȁ":"a",
"à":"a",
"ả":"a",
"ȃ":"a",
"ā":"a",
"ą":"a",
"ᶏ":"a",
"ẚ":"a",
"å":"a",
"ǻ":"a",
"ḁ":"a",
"ⱥ":"a",
"ã":"a",
"ꜳ":"aa",
"æ":"ae",
"ǽ":"ae",
"ǣ":"ae",
"ꜵ":"ao",
"ꜷ":"au",
"ꜹ":"av",
"ꜻ":"av",
"ꜽ":"ay",
"ḃ":"b",
"ḅ":"b",
"ɓ":"b",
"ḇ":"b",
"ᵬ":"b",
"ᶀ":"b",
"ƀ":"b",
"ƃ":"b",
"ɵ":"o",
"ć":"c",
"č":"c",
"ç":"c",
"ḉ":"c",
"ĉ":"c",
"ɕ":"c",
"ċ":"c",
"ƈ":"c",
"ȼ":"c",
"ď":"d",
"ḑ":"d",
"ḓ":"d",
"ȡ":"d",
"ḋ":"d",
"ḍ":"d",
"ɗ":"d",
"ᶑ":"d",
"ḏ":"d",
"ᵭ":"d",
"ᶁ":"d",
"đ":"d",
"ɖ":"d",
"ƌ":"d",
"ı":"i",
"ȷ":"j",
"ɟ":"j",
"ʄ":"j",
"ǳ":"dz",
"ǆ":"dz",
"é":"e",
"ĕ":"e",
"ě":"e",
"ȩ":"e",
"ḝ":"e",
"ê":"e",
"ế":"e",
"ệ":"e",
"ề":"e",
"ể":"e",
"ễ":"e",
"ḙ":"e",
"ë":"e",
"ė":"e",
"ẹ":"e",
"ȅ":"e",
"è":"e",
"ẻ":"e",
"ȇ":"e",
"ē":"e",
"ḗ":"e",
"ḕ":"e",
"ⱸ":"e",
"ę":"e",
"ᶒ":"e",
"ɇ":"e",
"ẽ":"e",
"ḛ":"e",
"ꝫ":"et",
"ḟ":"f",
"ƒ":"f",
"ᵮ":"f",
"ᶂ":"f",
"ǵ":"g",
"ğ":"g",
"ǧ":"g",
"ģ":"g",
"ĝ":"g",
"ġ":"g",
"ɠ":"g",
"ḡ":"g",
"ᶃ":"g",
"ǥ":"g",
"ḫ":"h",
"ȟ":"h",
"ḩ":"h",
"ĥ":"h",
"ⱨ":"h",
"ḧ":"h",
"ḣ":"h",
"ḥ":"h",
"ɦ":"h",
"ẖ":"h",
"ħ":"h",
"ƕ":"hv",
"í":"i",
"ĭ":"i",
"ǐ":"i",
"î":"i",
"ï":"i",
"ḯ":"i",
"ị":"i",
"ȉ":"i",
"ì":"i",
"ỉ":"i",
"ȋ":"i",
"ī":"i",
"į":"i",
"ᶖ":"i",
"ɨ":"i",
"ĩ":"i",
"ḭ":"i",
"ꝺ":"d",
"ꝼ":"f",
"ᵹ":"g",
"ꞃ":"r",
"ꞅ":"s",
"ꞇ":"t",
"ꝭ":"is",
"ǰ":"j",
"ĵ":"j",
"ʝ":"j",
"ɉ":"j",
"ḱ":"k",
"ǩ":"k",
"ķ":"k",
"ⱪ":"k",
"ꝃ":"k",
"ḳ":"k",
"ƙ":"k",
"ḵ":"k",
"ᶄ":"k",
"ꝁ":"k",
"ꝅ":"k",
"ĺ":"l",
"ƚ":"l",
"ɬ":"l",
"ľ":"l",
"ļ":"l",
"ḽ":"l",
"ȴ":"l",
"ḷ":"l",
"ḹ":"l",
"ⱡ":"l",
"ꝉ":"l",
"ḻ":"l",
"ŀ":"l",
"ɫ":"l",
"ᶅ":"l",
"ɭ":"l",
"ł":"l",
"ǉ":"lj",
"ſ":"s",
"ẜ":"s",
"ẛ":"s",
"ẝ":"s",
"ḿ":"m",
"ṁ":"m",
"ṃ":"m",
"ɱ":"m",
"ᵯ":"m",
"ᶆ":"m",
"ń":"n",
"ň":"n",
"ņ":"n",
"ṋ":"n",
"ȵ":"n",
"ṅ":"n",
"ṇ":"n",
"ǹ":"n",
"ɲ":"n",
"ṉ":"n",
"ƞ":"n",
"ᵰ":"n",
"ᶇ":"n",
"ɳ":"n",
"ñ":"n",
"ǌ":"nj",
"ó":"o",
"ŏ":"o",
"ǒ":"o",
"ô":"o",
"ố":"o",
"ộ":"o",
"ồ":"o",
"ổ":"o",
"ỗ":"o",
"ö":"o",
"ȫ":"o",
"ȯ":"o",
"ȱ":"o",
"ọ":"o",
"ő":"o",
"ȍ":"o",
"ò":"o",
"ỏ":"o",
"ơ":"o",
"ớ":"o",
"ợ":"o",
"ờ":"o",
"ở":"o",
"ỡ":"o",
"ȏ":"o",
"ꝋ":"o",
"ꝍ":"o",
"ⱺ":"o",
"ō":"o",
"ṓ":"o",
"ṑ":"o",
"ǫ":"o",
"ǭ":"o",
"ø":"o",
"ǿ":"o",
"õ":"o",
"ṍ":"o",
"ṏ":"o",
"ȭ":"o",
"ƣ":"oi",
"ꝏ":"oo",
"ɛ":"e",
"ᶓ":"e",
"ɔ":"o",
"ᶗ":"o",
"ȣ":"ou",
"ṕ":"p",
"ṗ":"p",
"ꝓ":"p",
"ƥ":"p",
"ᵱ":"p",
"ᶈ":"p",
"ꝕ":"p",
"ᵽ":"p",
"ꝑ":"p",
"ꝙ":"q",
"ʠ":"q",
"ɋ":"q",
"ꝗ":"q",
"ŕ":"r",
"ř":"r",
"ŗ":"r",
"ṙ":"r",
"ṛ":"r",
"ṝ":"r",
"ȑ":"r",
"ɾ":"r",
"ᵳ":"r",
"ȓ":"r",
"ṟ":"r",
"ɼ":"r",
"ᵲ":"r",
"ᶉ":"r",
"ɍ":"r",
"ɽ":"r",
"ↄ":"c",
"ꜿ":"c",
"ɘ":"e",
"ɿ":"r",
"ś":"s",
"ṥ":"s",
"š":"s",
"ṧ":"s",
"ş":"s",
"ŝ":"s",
"ș":"s",
"ṡ":"s",
"ṣ":"s",
"ṩ":"s",
"ʂ":"s",
"ᵴ":"s",
"ᶊ":"s",
"ȿ":"s",
"ɡ":"g",
"ᴑ":"o",
"ᴓ":"o",
"ᴝ":"u",
"ť":"t",
"ţ":"t",
"ṱ":"t",
"ț":"t",
"ȶ":"t",
"ẗ":"t",
"ⱦ":"t",
"ṫ":"t",
"ṭ":"t",
"ƭ":"t",
"ṯ":"t",
"ᵵ":"t",
"ƫ":"t",
"ʈ":"t",
"ŧ":"t",
"ᵺ":"th",
"ɐ":"a",
"ᴂ":"ae",
"ǝ":"e",
"ᵷ":"g",
"ɥ":"h",
"ʮ":"h",
"ʯ":"h",
"ᴉ":"i",
"ʞ":"k",
"ꞁ":"l",
"ɯ":"m",
"ɰ":"m",
"ᴔ":"oe",
"ɹ":"r",
"ɻ":"r",
"ɺ":"r",
"ⱹ":"r",
"ʇ":"t",
"ʌ":"v",
"ʍ":"w",
"ʎ":"y",
"ꜩ":"tz",
"ú":"u",
"ŭ":"u",
"ǔ":"u",
"û":"u",
"ṷ":"u",
"ü":"u",
"ǘ":"u",
"ǚ":"u",
"ǜ":"u",
"ǖ":"u",
"ṳ":"u",
"ụ":"u",
"ű":"u",
"ȕ":"u",
"ù":"u",
"ủ":"u",
"ư":"u",
"ứ":"u",
"ự":"u",
"ừ":"u",
"ử":"u",
"ữ":"u",
"ȗ":"u",
"ū":"u",
"ṻ":"u",
"ų":"u",
"ᶙ":"u",
"ů":"u",
"ũ":"u",
"ṹ":"u",
"ṵ":"u",
"ᵫ":"ue",
"ꝸ":"um",
"ⱴ":"v",
"ꝟ":"v",
"ṿ":"v",
"ʋ":"v",
"ᶌ":"v",
"ⱱ":"v",
"ṽ":"v",
"ꝡ":"vy",
"ẃ":"w",
"ŵ":"w",
"ẅ":"w",
"ẇ":"w",
"ẉ":"w",
"ẁ":"w",
"ⱳ":"w",
"ẘ":"w",
"ẍ":"x",
"ẋ":"x",
"ᶍ":"x",
"ý":"y",
"ŷ":"y",
"ÿ":"y",
"ẏ":"y",
"ỵ":"y",
"ỳ":"y",
"ƴ":"y",
"ỷ":"y",
"ỿ":"y",
"ȳ":"y",
"ẙ":"y",
"ɏ":"y",
"ỹ":"y",
"ź":"z",
"ž":"z",
"ẑ":"z",
"ʑ":"z",
"ⱬ":"z",
"ż":"z",
"ẓ":"z",
"ȥ":"z",
"ẕ":"z",
"ᵶ":"z",
"ᶎ":"z",
"ʐ":"z",
"ƶ":"z",
"ɀ":"z",
"ﬀ":"ff",
"ﬃ":"ffi",
"ﬄ":"ffl",
"ﬁ":"fi",
"ﬂ":"fl",
"ĳ":"ij",
"œ":"oe",
"ﬆ":"st",
"ₐ":"a",
"ₑ":"e",
"ᵢ":"i",
"ⱼ":"j",
"ₒ":"o",
"ᵣ":"r",
"ᵤ":"u",
"ᵥ":"v",
"ₓ":"x"};
String.prototype.latinise=function(){return this.replace(/[^A-Za-z0-9\[\] ]/g,function(a){return Latinise.latin_map[a]||a})};
String.prototype.latinize=String.prototype.latinise;
String.prototype.isLatin=function(){return this==this.latinise()}