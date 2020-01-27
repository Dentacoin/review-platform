var ajax_is_running = false;
var modernFieldsUpdate;
jQuery(document).ready(function($){

	$('.signin-form-wrapper form').submit( function(e) {
		e.preventDefault();
		showPopup('popup-register');
		modernFieldsUpdate();
		$('.switch-forms').first().click();
		$('#dentist-email').val( $(this).find('input[name="email"]').val() );
		$('#dentist-password').val( $(this).find('input[name="password"]').val() );
		$('#dentist-password-repeat').val( $(this).find('input[name="password-repeat"]').val() );
		prepareLoginFucntion( function() {
			$('.go-to-next:visible').click();
		});
		
    } );

    if( $('#dentist-email').val() ) {
		showPopup('popup-register');
    }
    var $carousel = $('.flickity-testimonial');

	$('.testimonial img').on('load', function() {
		$carousel.flickity({
	    	autoPlay: true,
			wrapAround: true,
			cellAlign: 'left',
			pageDots: false,
			groupCells: 1,
			adaptiveHeight: true,
		});
	});

	setTimeout( function() {

		$carousel.flickity({
	    	autoPlay: true,
			wrapAround: true,
			cellAlign: 'left',
			pageDots: false,
			groupCells: 1,
			adaptiveHeight: true,
		});
	}, 1000);

	if ($(window).innerWidth() < 768) {
		$('.mobile-flickity .left').children().appendTo('.mobile-flickity');
		$('.mobile-flickity .left').remove();
		$('.mobile-flickity .right').children().appendTo('.mobile-flickity');
		$('.mobile-flickity .right').remove();

		$('.mobile-flickity').flickity({
	    	//autoPlay: true,
			wrapAround: true,
			cellAlign: 'left',
			pageDots: true,
			prevNextButtons: false,
			groupCells: 1,
			adaptiveHeight: true,
		});
	}

	$('.lead-magnet-form-step2').submit( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        var that = $(this);
        $('.loader').fadeIn();
        $('.loader-mask').fadeIn();
        $('.loader-text').fadeIn();
        //$('#magnet-submit').append('<div class="loader"><i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i></div>');

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    fbq('track', 'TRPMagnetComplete');

                    gtag('event', 'SeeScore', {
                        'event_category': 'LeadMagnet',
                        'event_label': 'ReplyToReviews',
                    });

                    setTimeout( function() {
                        window.location.href = data.url;
                    }, 8000);
                } else {
                	console.log('error');
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;
    } );

    $('.lead-magnet-radio').change( function() {
    	$(this).closest('.answer-radios-magnet').find('label').removeClass('active');
    	$(this).closest('label').addClass('active');
    });

    $('.lead-magnet-checkbox').change( function() {
    	$(this).closest('label').toggleClass('active');

        if ($(this).hasClass('disabler')) {
            if ($(this).prop('checked')) {

                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('disabled', true);
                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('checked', false);
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').addClass('disabled-label');
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').removeClass('active');
            } else {
                $(this).closest('.buttons-list').find('.lead-magnet-checkbox').not(this).prop('disabled', false);
                $(this).closest('.buttons-list').find('.magnet-label:not(.disabler-label)').removeClass('disabled-label');
            }
        }
    });

    $('.lead-magnet-radio').click( function() {
        if ($(this).attr('name') == 'answer-1') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Priority',
            });
        } else if ($(this).attr('name') == 'answer-2') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Tool',
            });
        } else if ($(this).attr('name') == 'answer-4') {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'Frequency',
            });
        }

        if ($(this).attr('name') == 'answer-5') {
            $(this).closest('form').find('button').trigger('click');
        } else {

            $('.flickity-magnet').flickity('next');
        }

    });

    $('.magnet-validator').click( function() {
    		
	    if($(this).closest('.answer-radios-magnet').find('input:checked').length) {
            gtag('event', 'Next', {
                'event_category': 'LeadMagnet',
                'event_label': 'AskForReviews',
            });

	    	if ($(this).hasClass('validator-skip')) {
	    		if ($(this).closest('.answer-radios-magnet').find('input:checked').val() == '4') {
	    			$('.flickity-magnet').flickity( 'select', 4 );
	    		} else {
	    			$('.flickity-magnet').flickity('next');
	    		}
	    	} else {
	    		$('.flickity-magnet').flickity('next');
	    	}    		
    	} else {
    		$(this).closest('.flickity-viewport').css('height', $(this).closest('.flickity-viewport').height() + 76);
    		$(this).closest('.answer-radios-magnet').find('.alert-warning').show();
    	}

    });

    $('#open-magnet').click( function() {
        gtag('event', 'Open', {
            'event_category': 'LeadMagnet',
            'event_label': 'Popup',
        });
    });

    $('.first-form-button').click( function(e) {
        e.preventDefault();

        if(ajax_is_running) {
            return;
        }
        ajax_is_running = true;
        var that = $(this);
        $.post( 
            $(this).attr('data-validator'), 
            $('#lead-magnet-form-step2').serialize(), 
            function( data ) {
                if(data.success) {

                    if (!Cookies.get('marketing_cookies') && !$('#ariticform_wrapper_leadmagnetform').length) {
                        basic.cookies.set('marketing_cookies', 1);

                        $('body').append("<script>\
                            (function(w,d,t,u,n,a,m){\
                                if(typeof w['AriticTrackingObject'] !== 'undefined') return;w['AriticTrackingObject']=n;\
                                w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),\
                                m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)\
                            })(window,document,'script','https://dentacoin.ariticapp.com/ma/atc.js','at');\
                        </script>");

                        $.getScript('https://dentacoin.ariticapp.com/ma/patc.js', function() {
                            console.log('patc')
                        } );

                        $('body').append('<script type="text/javascript">\
                            function LeadMagenet() {\
                                setTimeout( function() {\
                                    _aaq.push(["setContactFields", {\
                                        firstname:document.getElementById("magnet-name").value,\
                                        website:document.getElementById("magnet-website").value,\
                                        email:document.getElementById("magnet-email").value,\
                                    }]);\
                                    _aaq.push("rememberConsentGiven", false, 3);\
                                    _aaq.push(["trackPageView"]);\
                                }, 5000);\
                            }\
                        </script>');

                        $('head').append("<script type='text/javascript'>\
                            if (typeof AriticSDKLoaded == 'undefined') {\
                                var AriticSDKLoaded = true;\
                                var head            = document.getElementsByTagName('head')[0];\
                                var script          = document.createElement('script');\
                                script.type         = 'text/javascript';\
                                script.src          = 'https://ariticpinpoint.dentacoin.com/ma/media/js/aritic-form.min.js';\
                                script.onload       = function() {\
                                    AriticSDK.onLoad();\
                                };\
                                head.appendChild(script);\
                                var AriticDomain = 'https://ariticpinpoint.dentacoin.com/ma';\
                                var AriticLang   = {\
                                    'submittingMessage': 'Please wait...'\
                                }\
                            }\
                        </script>\
                        ");

                        $('body').append('<style type="text/css"> body{font-size: 13px; line-height: 1.3856}audio, canvas, img, svg, video{max-width: 100%; height: auto; box-sizing: border-box}.ariticform_wrapper{max-width: 100%}.ariticform-innerform{width: 100%}.ariticform-name{font-weight: 700; font-size: 1.5em; margin-bottom: 3px}.ariticform-description{margin-top: 2px; margin-bottom: 10px}.ariticform-error{margin-bottom: 10px; color: red}.ariticform-message{margin-bottom: 10px; color: green}.ariticform-row{display: block; padding: 10px}.ariticform-label{font-size: 1.1em; display: block; margin-bottom: 5px}.ariticform-row.ariticform-required .ariticform-label:after{color: #e32; content: " *"; display: inline}.ariticform-helpmessage{display: block; font-size: .9em; margin-bottom: 3px}.ariticform-errormsg{display: block; color: red; margin-top: 2px}.ariticform-input, .ariticform-selectbox, .ariticform-textarea{color: #000; width: 100%; padding: .5em .5em; border: 1px solid #ccc; background: #fff; box-shadow: 0 0 0 #fff inset; border-radius: 4px; box-sizing: border-box}.ariticform-checkboxgrp-label{font-weight: 400}.ariticform-radiogrp-label{font-weight: 400}.ariticform-pagebreak.btn-default{color: #5d6c7c; background-color: #fff}.ariticform-button, .ariticform-pagebreak{display: inline-block; margin-bottom: 0; font-weight: 600; text-align: center; vertical-align: middle; cursor: pointer; background-image: none; border: 1px solid transparent; white-space: nowrap; padding: 6px 12px; font-size: 13px; line-height: 1.3856; border-radius: 3px; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none}.ariticform-pagebreak-wrapper .ariticform-button-wrapper{display: inline}.ariticform_wrapper{margin: 0 auto; display: -ms-flexbox; display: -webkit-flex; display: flex; -ms-flex-wrap: wrap; -webkit-flex-wrap: wrap}.ariticform-page-wrapper{width: 100%; display: -ms-flexbox; display: -webkit-flex; display: flex; -ms-flex-wrap: wrap; -webkit-flex-wrap: wrap}.ariticform-row{float: left; box-sizing: border-box; width: 100%}.ariticform-col-1-2{width: 50%}.ariticform-col-1-3{width: 33.3%}.ariticform_wrapper form{width: 100%}.ariticform-aligncenter{text-align: center}.ariticform-alignleft{text-align: left}.ariticform-alignright{text-align: right}.ariticform_wrapper .ariticform-single-col .ariticform-label{width: 30%; float: left}.ariticform_wrapper .ariticform-single-col .ariticform-checkboxgrp-input, .ariticform_wrapper .ariticform-single-col .ariticform-input, .ariticform_wrapper .ariticform-single-col .ariticform-radiogrp-input, .ariticform_wrapper .ariticform-single-col .ariticform-textarea{width: 70%; float: left}.ariticform_wrapper .ariticform-single-col .ariticform-checkboxgrp-input.ariticform-withoutlabel, .ariticform_wrapper .ariticform-single-col .ariticform-input.ariticform-withoutlabel, .ariticform_wrapper .ariticform-single-col .ariticform-radiogrp-input.ariticform-withoutlabel, .ariticform_wrapper .ariticform-single-col .ariticform-textarea.ariticform-withoutlabel{width: 100%}.ariticform-innerform{display: -ms-flexbox; display: -webkit-flex; display: flex; -ms-flex-wrap: wrap; -webkit-flex-wrap: wrap; flex-wrap: wrap}.ariticform_wrapper{width: 720px}.ariticform-label-left, .ariticform-label-right{width: 150px}.ariticform-label{white-space: normal}.ariticform-label-left{display: inline-block; white-space: normal; float: left; text-align: left}.ariticform-label-right{display: inline-block; white-space: normal; float: left; text-align: right}.ariticform-label-top{white-space: normal; display: block; float: none; text-align: left}.form-radio-item label:before{top: 0}.form-all{font-size: 16px}.ariticform-label{font-weight: 700}.form-checkbox-item label, .form-radio-item label{font-weight: 400}.ariticform_wrapper{background-color: #fff}.ariticform_wrapper{color: #555}.ariticform-label, label{font-size: 12}.ariticform-label, label{color: #6f6f6f}.ariticform-label{color: #555}.ariticform-label, label{color: #6f6f6f}.form-all{width: 720px}.ariticform_wrapper{padding: 0}.ariticform_wrapper{border-radius: unset}.ariticform-freehtml, .ariticform-label, .ariticform-row, .ariticform_wrapper{font-family: Arial, Helvetica, sans-serif}.ariticform-input, .ariticform-textarea{background-color: #fff}.ariticform-button{background-color: #fff; color: #000; border-color: #fff}.ariticform-button-wrapper{text-align: left}.ariticform-button{text-transform: uppercase; border-radius: unset}.ariticform-button{border-color: #000}.ariticform-input, .ariticform-textarea{border-radius: unset}.ariticform_wrapper{box-sizing: border-box;}</style><div id="ariticform_wrapper_leadmagnetform" class="ariticform_wrapper"> <form autocomplete="false" role="form" method="post" action="https://ariticpinpoint.dentacoin.com/ma/form/submit?formId=13" id="ariticform_leadmagnetform" data-aritic-form="leadmagnetform" data-aritic-id="13" enctype="multipart/form-data"> <div class="ariticform-error" id="ariticform_leadmagnetform_error"></div><div class="ariticform-message" id="ariticform_leadmagnetform_message"></div><div class="ariticform-innerform"> <div class="ariticform-page-wrapper ariticform-page-1" data-aritic-form-page="1"> <div id="ariticform_leadmagnetform_practice_name" data-validate="practice_name" data-validation-type="firstname" class="ariticform-row ariticform-text ariticform-field-1 ariticform-col-1-1 ariticform-double-col ariticform-required"> <label id="ariticform_label_leadmagnetform_practice_name" for="ariticform_input_leadmagnetform_practice_name" class="ariticform-label">Practice name</label> <input id="ariticform_input_leadmagnetform_practice_name" name="ariticform[practice_name]" value="" class="ariticform-input" type="text"> <span class="ariticform-errormsg" style="display: none;">This is required.</span> </div><div id="ariticform_leadmagnetform_website" data-validate="website" data-validation-type="text" class="ariticform-row ariticform-text ariticform-field-2 ariticform-col-1-1 ariticform-double-col ariticform-required"> <label id="ariticform_label_leadmagnetform_website" for="ariticform_input_leadmagnetform_website" class="ariticform-label">Website</label> <input id="ariticform_input_leadmagnetform_website" name="ariticform[website]" value="" class="ariticform-input" type="text"> <span class="ariticform-errormsg" style="display: none;">This is required.</span> </div><div id="ariticform_leadmagnetform_country" class="ariticform-row ariticform-select ariticform-field-3 ariticform-col-1-1 ariticform-double-col"> <label id="ariticform_label_leadmagnetform_country" for="ariticform_input_leadmagnetform_country" class="ariticform-label">Country</label> <select id="ariticform_input_leadmagnetform_country" name="ariticform[country]" value="" class="ariticform-selectbox"> <option value=""></option> <option value="Afghanistan">Afghanistan</option> <option value="Åland Islands">Åland Islands</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antarctica">Antarctica</option> <option value="Antigua and Barbuda">Antigua and Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire, Saint Eustatius and Saba">Bonaire, Saint Eustatius and Saba</option> <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Bouvet Island">Bouvet Island</option> <option value="Brazil">Brazil</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cape Verde">Cape Verde</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="Democratic Republic of the Congo">Democratic Republic of the Congo</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guernsey">Guernsey</option> <option value="Guinea">Guinea</option> <option value="Guinea Bissau">Guinea Bissau</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Heard Island and McDonald Islands">Heard Island and McDonald Islands</option> <option value="Holy See">Holy See</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="India">India</option> <option value="Indonesia">Indonesia</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Ivory Coast">Ivory Coast</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jersey">Jersey</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macao">Macao</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malawi">Malawi</option> <option value="Malaysia">Malaysia</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Micronesia">Micronesia</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montenegro">Montenegro</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Namibia">Namibia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherlands">Netherlands</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="North Korea">North Korea</option> <option value="Northern Mariana Islands">Northern Mariana Islands</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau">Palau</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Philippines">Philippines</option> <option value="Pitcairn">Pitcairn</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of the Congo">Republic of the Congo</option> <option value="Réunion">Réunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="Saint Barthelemy">Saint Barthelemy</option> <option value="Saint Helena, Ascension and Tristan da Cunha">Saint Helena, Ascension and Tristan da Cunha</option> <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option> <option value="Saint Lucia">Saint Lucia</option> <option value="Saint Martin">Saint Martin</option> <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option> <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option> <option value="Samoa">Samoa</option> <option value="San Marino">San Marino</option> <option value="Sao Tome and Principe">Sao Tome and Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Serbia">Serbia</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="South Georgia and the South Sandwich Islands">South Georgia and the South Sandwich Islands</option> <option value="South Korea">South Korea</option> <option value="South Sudan">South Sudan</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad and Tobago">Trinidad and Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks and Caicos Islands">Turks and Caicos Islands</option> <option value="Tuvalu">Tuvalu</option> <option value="United Kingdom">United Kingdom</option> <option value="United States">United States</option> <option value="Unknown">Unknown</option> <option value="Uganda">Uganda</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Emirates">United Arab Emirates</option> <option value="Uruguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (British)">Virgin Islands (British)</option> <option value="Virgin Islands (U.S.)">Virgin Islands (U.S.)</option> <option value="Wallis and Futuna">Wallis and Futuna</option> <option value="Western Sahara">Western Sahara</option> <option value="Yemen">Yemen</option> <option value="Yugoslavia">Yugoslavia</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option> </select> <span class="ariticform-errormsg" style="display: none;"></span> </div><div id="ariticform_leadmagnetform_email" data-validate="email" data-validation-type="email" class="ariticform-row ariticform-email ariticform-field-4 ariticform-col-1-1 ariticform-double-col ariticform-required"> <label id="ariticform_label_leadmagnetform_email" for="ariticform_input_leadmagnetform_email" class="ariticform-label">Email</label> <input id="ariticform_input_leadmagnetform_email" name="ariticform[email]" value="" class="ariticform-input" type="email"> <span class="ariticform-errormsg" style="display: none;">This is required.</span> </div><div id="ariticform_leadmagnetform_gdpr_checkbox" data-validate="gdpr_checkbox" data-validation-type="checkboxgrp" class="ariticform-row ariticform-checkboxgrp ariticform-field-5 ariticform-col-1-1 ariticform-double-col ariticform-required"> <div class="ariticform-checkboxgrp-input"> <label id="ariticform_checkboxgrp_label_gdpr_checkbox" for="ariticform_checkboxgrp_checkbox_gdpr_checkbox" style="width:100%;"> <input name="ariticform[gdpr_checkbox][]" id="ariticform_checkboxgrp_checkbox_gdpr_checkbox" type="checkbox" value="2" style="float: left;margin-right: 10px;"> <p>By submitting the form, you agree to our <a href="https://dentacoin.com/privacy-policy">PrivacyPolicy</a>.</p></label> </div><span class="ariticform-errormsg" style="display: none;">This is required.</span> </div><div id="ariticform_leadmagnetform_submit" class="ariticform-row ariticform-button-wrapper ariticform-field-6 ariticform-col-1-1 ariticform-single-col"> <button type="submit" name="ariticform[submit]" id="ariticform_input_leadmagnetform_submit" value="" class="ariticform-button btn btn-default">Submit</button> </div></div></div><input type="hidden" name="ariticform[formId]" id="ariticform_leadmagnetform_id" value="13"> <input type="hidden" name="ariticform[return]" id="ariticform_leadmagnetform_return" value=""> <input type="hidden" name="ariticform[formName]" id="ariticform_leadmagnetform_name" value="leadmagnetform"> </form></div>');

                        // $.getScript('//dentacoin.ariticapp.com/ma/form/generate.js?id=13', function() {
                        //     console.log('gdpr')
                        // } );
                    }


                    if (!Cookies.get('performance_cookies')) {
                        basic.cookies.set('performance_cookies', 1);
                    }
                    if (!Cookies.get('functionality_cookies')) {
                        basic.cookies.set('functionality_cookies', 1);
                    }
                    if (!Cookies.get('strictly_necessary_policy')) {
                        basic.cookies.set('strictly_necessary_policy', 1);
                    }

                    if ($('.privacy-policy-cookie').length) {
                        $('.privacy-policy-cookie').hide();
                    }

                    that.closest('.magnet-content').next().show();
                    that.closest('.magnet-content').hide();

                    that.closest('.popup-inner').find('.colorful-tabs').find('.col').removeClass('active');
                    that.closest('.popup-inner').find('.colorful-tabs').find('.second-step').addClass('active');

                    var $carousel = $('.flickity-magnet');

                    $carousel.flickity({
                        //wrapAround: true,
                        adaptiveHeight: true,
                        draggable: false,
                        pageDots: true,
                    });

                    fbq('track', 'TRPMagnetStart');

                    gtag('event', 'RunTest', {
                        'event_category': 'LeadMagnet',
                        'event_label': 'ContactDetails',
                    });

                    setTimeout( function() {
                        
                        $('#ariticform_input_leadmagnetform_practice_name').val( $('#magnet-name').val() );
                        $('#ariticform_input_leadmagnetform_website').val( $('#magnet-website').val() );
                        $('#ariticform_input_leadmagnetform_country').val( $('#magnet-country option:selected').text() );
                        $('#ariticform_input_leadmagnetform_email').val( $('#magnet-email').val() );
                        $('#ariticform_checkboxgrp_checkbox_gdpr_checkbox').prop('checked', true);

                        $('#ariticform_input_leadmagnetform_submit').trigger('click');
                    }, 2000);


                } else {
                    that.closest('form').find('.ajax-alert').remove();
                    for(var i in data.messages) {
                        that.closest('form').find('[name="'+i+'"]').addClass('has-error');
                        that.closest('form').find('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert" error="'+i+'">'+data.messages[i]+'</div>'); 

                        if (that.closest('form').find('[name="'+i+'"]').closest('.agree-label').length) {
                            that.closest('form').find('[name="'+i+'"]').closest('.agree-label').addClass('has-error');
                        }  
                    }
                }
                ajax_is_running = false;
            }, 
            "json"
        );

    } );

    $('.magnet-popup').click( function() {
        var that = $(this);

        $.ajax({
            type: "GET",
            url: that.attr('data-url'),
            dataType: 'json',
            success: function(ret) {
                if(ret.session) {
                    window.location.href = ret.url;
                } else {
                    showPopup('popup-lead-magnet');
                }
            },
            error: function(ret) {
                console.log('error');
            }
        });
        
    });
    
});
