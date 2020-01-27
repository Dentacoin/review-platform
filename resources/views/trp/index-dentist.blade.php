@extends('trp')

@section('content')

	<div class="welcome-dentist-section">
		<div class="signin-top">
	    	<h1>
	    		{{ trans('trp.page.index-dentist.title') }}
	    	</h1>

	    	<p>
	    		{!! nl2br(trans('trp.page.index-dentist.subtitle')) !!}
	    	</p>

			<div class="ratings biggest">
				<div class="stars">
					<div class="bar" style="width: 100%;">
					</div>
				</div>
			</div>

			<div class="tac button-wrap">
				<a href="javascript:;" class="button button-sign-up-dentist" data-popup="popup-register">
	    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
	    		</a>
	    	</div>

			@if($unsubscribed)
				<div class="alert alert-info">
					{{ trans('trp.page.index-dentist.unsubscribed') }}
				</div>
			@endif

	    </div>

	    <div class="signin-form-wrapper">
	    	<img src="{{ url('img-trp/dentacoin-trusted-reviews-dentist-front-page.png') }}" alt="Dentacoin trusted reviews dentist front page">
	    	<div class="container clearfix">
	    		<form class="signin-form tablet-fixes">

					<div class="form-inner">
						<div class="modern-field">
							<input type="email" name="email" id="dentist-mail" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="dentist-mail">
								<span>{{ trans('trp.page.index-dentist.email') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password" id="dentist-pass" class="modern-input" autocomplete="off">
							<label for="dentist-pass">
								<span>{{ trans('trp.page.index-dentist.password') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password-repeat" id="dentist-pass-repeat" class="modern-input" autocomplete="off">
							<label for="dentist-pass-repeat">
								<span>{{ trans('trp.page.index-dentist.repeat-password') }}</span>
							</label>
						</div>

						<div class="tac">
							<input type="submit" value="{{ trans('trp.page.index-dentist.signup') }}" class="button button-sign-up-dentist">
						</div>
					</div>

					<p class="have-account">
						{!! nl2br(trans('trp.page.index-dentist.have-account', [
							'link' => '<a href="javascript:;" data-popup="popup-login">',
							'endlink' => '</a>',
						])) !!}					
					</p>

	    		</form>
	    	</div>
	    </div>
	</div>


    <div class="container section-dentist-info">
    	<h2 class="tac">
    		{!! nl2br(trans('trp.page.index-dentist.usp-title')) !!}
    	</h2>

    	<div class="flex">
    		<div class="col tac">
    			<img src="{{ url('img-trp/dentacoin-attract-new-patients-icon.png') }}" alt="Dentacoin attract new patients icon">
    			<div class="info-padding">
	    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-1-title')) !!}</h3>
	    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-1-description')) !!}</p>
	    		</div>
    		</div>
    		<div class="col tac">
    			<img src="{{ url('img-trp/dentacoin-get-more-reviews-icon.png') }}" alt="Dentacoin get more reviews icon">   
    			<div class="info-padding"> 			
	    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-2-title')) !!}</h3>
	    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-2-description')) !!}</p>
	    		</div>
    		</div>
    	</div>

    	<div class="flex">
    		<div class="col tac">
    			<img src="{{ url('img-trp/dentacoin-better-google-ranking-icon.png') }}" alt="Dentacoin better google ranking icon">
    			<div class="info-padding">
	    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-3-title')) !!}</h3>
	    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-3-description')) !!}</p>
	    		</div>
    		</div>
    		<div class="col tac">
    			<img src="{{ url('img-trp/dentacoin-better-online-reputation-icon.png') }}" alt="Dentacoin better online reputation icon">
    			<div class="info-padding">
	    			<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-4-title')) !!}</h3>
	    			<p>{!! nl2br(trans('trp.page.index-dentist.usp.step-4-description')) !!}</p>
	    		</div>
    		</div>
    	</div>

    	<div class="tac button-wrap">
			<a href="javascript:;" class="button button-sign-up-dentist" data-popup="popup-register">
    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
    		</a>
    	</div>

		<div class="tac">
			<a href="javascript:;" class="button button-yellow magnet-popup" id="open-magnet" data-url="{{ getLangUrl('lead-magnet-session') }}">{{ trans('trp.page.index-dentist.button-lead-magnet') }}</a>
		</div>
    </div>

    @if(!empty($_COOKIE['marketing_cookies']))

    	

		<style type="text/css">
		    body {
		        font-size: 13px;
		        line-height: 1.3856
		    }
		    
		    audio,
		    canvas,
		    img,
		    svg,
		    video {
		        max-width: 100%;
		        height: auto;
		        box-sizing: border-box
		    }
		    
		    .ariticform_wrapper {
		        max-width: 100%
		    }
		    
		    .ariticform-innerform {
		        width: 100%
		    }
		    
		    .ariticform-name {
		        font-weight: 700;
		        font-size: 1.5em;
		        margin-bottom: 3px
		    }
		    
		    .ariticform-description {
		        margin-top: 2px;
		        margin-bottom: 10px
		    }
		    
		    .ariticform-error {
		        margin-bottom: 10px;
		        color: red
		    }
		    
		    .ariticform-message {
		        margin-bottom: 10px;
		        color: green
		    }
		    
		    .ariticform-row {
		        display: block;
		        padding: 10px
		    }
		    
		    .ariticform-label {
		        font-size: 1.1em;
		        display: block;
		        margin-bottom: 5px
		    }
		    
		    .ariticform-row.ariticform-required .ariticform-label:after {
		        color: #e32;
		        content: " *";
		        display: inline
		    }
		    
		    .ariticform-helpmessage {
		        display: block;
		        font-size: .9em;
		        margin-bottom: 3px
		    }
		    
		    .ariticform-errormsg {
		        display: block;
		        color: red;
		        margin-top: 2px
		    }
		    
		    .ariticform-input,
		    .ariticform-selectbox,
		    .ariticform-textarea {
		        color: #000;
		        width: 100%;
		        padding: .5em .5em;
		        border: 1px solid #ccc;
		        background: #fff;
		        box-shadow: 0 0 0 #fff inset;
		        border-radius: 4px;
		        box-sizing: border-box
		    }
		    
		    .ariticform-checkboxgrp-label {
		        font-weight: 400
		    }
		    
		    .ariticform-radiogrp-label {
		        font-weight: 400
		    }
		    
		    .ariticform-pagebreak.btn-default {
		        color: #5d6c7c;
		        background-color: #fff
		    }
		    
		    .ariticform-button,
		    .ariticform-pagebreak {
		        display: inline-block;
		        margin-bottom: 0;
		        font-weight: 600;
		        text-align: center;
		        vertical-align: middle;
		        cursor: pointer;
		        background-image: none;
		        border: 1px solid transparent;
		        white-space: nowrap;
		        padding: 6px 12px;
		        font-size: 13px;
		        line-height: 1.3856;
		        border-radius: 3px;
		        -webkit-user-select: none;
		        -moz-user-select: none;
		        -ms-user-select: none;
		        user-select: none
		    }
		    
		    .ariticform-pagebreak-wrapper .ariticform-button-wrapper {
		        display: inline
		    }
		    
		    .ariticform_wrapper {
		        margin: 0 auto;
		        display: -ms-flexbox;
		        display: -webkit-flex;
		        display: flex;
		        -ms-flex-wrap: wrap;
		        -webkit-flex-wrap: wrap
		    }
		    
		    .ariticform-page-wrapper {
		        width: 100%;
		        display: -ms-flexbox;
		        display: -webkit-flex;
		        display: flex;
		        -ms-flex-wrap: wrap;
		        -webkit-flex-wrap: wrap
		    }
		    
		    .ariticform-row {
		        float: left;
		        box-sizing: border-box;
		        width: 100%
		    }
		    
		    .ariticform-col-1-2 {
		        width: 50%
		    }
		    
		    .ariticform-col-1-3 {
		        width: 33.3%
		    }
		    
		    .ariticform_wrapper form {
		        width: 100%
		    }
		    
		    .ariticform-aligncenter {
		        text-align: center
		    }
		    
		    .ariticform-alignleft {
		        text-align: left
		    }
		    
		    .ariticform-alignright {
		        text-align: right
		    }
		    
		    .ariticform_wrapper .ariticform-single-col .ariticform-label {
		        width: 30%;
		        float: left
		    }
		    
		    .ariticform_wrapper .ariticform-single-col .ariticform-checkboxgrp-input,
		    .ariticform_wrapper .ariticform-single-col .ariticform-input,
		    .ariticform_wrapper .ariticform-single-col .ariticform-radiogrp-input,
		    .ariticform_wrapper .ariticform-single-col .ariticform-textarea {
		        width: 70%;
		        float: left
		    }
		    
		    .ariticform_wrapper .ariticform-single-col .ariticform-checkboxgrp-input.ariticform-withoutlabel,
		    .ariticform_wrapper .ariticform-single-col .ariticform-input.ariticform-withoutlabel,
		    .ariticform_wrapper .ariticform-single-col .ariticform-radiogrp-input.ariticform-withoutlabel,
		    .ariticform_wrapper .ariticform-single-col .ariticform-textarea.ariticform-withoutlabel {
		        width: 100%
		    }
		    
		    .ariticform-innerform {
		        display: -ms-flexbox;
		        display: -webkit-flex;
		        display: flex;
		        -ms-flex-wrap: wrap;
		        -webkit-flex-wrap: wrap;
		        flex-wrap: wrap
		    }
		    
		    .ariticform_wrapper {
		        width: 720px
		    }
		    
		    .ariticform-label-left,
		    .ariticform-label-right {
		        width: 150px
		    }
		    
		    .ariticform-label {
		        white-space: normal
		    }
		    
		    .ariticform-label-left {
		        display: inline-block;
		        white-space: normal;
		        float: left;
		        text-align: left
		    }
		    
		    .ariticform-label-right {
		        display: inline-block;
		        white-space: normal;
		        float: left;
		        text-align: right
		    }
		    
		    .ariticform-label-top {
		        white-space: normal;
		        display: block;
		        float: none;
		        text-align: left
		    }
		    
		    .form-radio-item label:before {
		        top: 0
		    }
		    
		    .form-all {
		        font-size: 16px
		    }
		    
		    .ariticform-label {
		        font-weight: 700
		    }
		    
		    .form-checkbox-item label,
		    .form-radio-item label {
		        font-weight: 400
		    }
		    
		    .ariticform_wrapper {
		        background-color: #fff
		    }
		    
		    .ariticform_wrapper {
		        color: #555
		    }
		    
		    .ariticform-label,
		    label {
		        font-size: 12
		    }
		    
		    .ariticform-label,
		    label {
		        color: #6f6f6f
		    }
		    
		    .ariticform-label {
		        color: #555
		    }
		    
		    .ariticform-label,
		    label {
		        color: #6f6f6f
		    }
		    
		    .form-all {
		        width: 720px
		    }
		    
		    .ariticform_wrapper {
		        padding: 0
		    }
		    
		    .ariticform_wrapper {
		        border-radius: unset
		    }
		    
		    .ariticform-freehtml,
		    .ariticform-label,
		    .ariticform-row,
		    .ariticform_wrapper {
		        font-family: Arial, Helvetica, sans-serif
		    }
		    
		    .ariticform-input,
		    .ariticform-textarea {
		        background-color: #fff
		    }
		    
		    .ariticform-button {
		        background-color: #fff;
		        color: #000;
		        border-color: #fff
		    }
		    
		    .ariticform-button-wrapper {
		        text-align: left
		    }
		    
		    .ariticform-button {
		        text-transform: uppercase;
		        border-radius: unset
		    }
		    
		    .ariticform-button {
		        border-color: #000
		    }
		    
		    .ariticform-input,
		    .ariticform-textarea {
		        border-radius: unset
		    }
		    
		    .ariticform_wrapper {
		        box-sizing: border-box;
		    }
		</style>

		<div id="ariticform_wrapper_leadmagnetform" class="ariticform_wrapper">
		    <form autocomplete="false" role="form" method="post" action="https://ariticpinpoint.dentacoin.com/ma/form/submit?formId=13" id="ariticform_leadmagnetform" data-aritic-form="leadmagnetform" data-aritic-id="13" enctype="multipart/form-data">
		        <div class="ariticform-error" id="ariticform_leadmagnetform_error"></div>
		        <div class="ariticform-message" id="ariticform_leadmagnetform_message"></div>
		        <div class="ariticform-innerform">

		            <div class="ariticform-page-wrapper ariticform-page-1" data-aritic-form-page="1">

		                <div id="ariticform_leadmagnetform_practice_name" data-validate="practice_name" data-validation-type="firstname" class="ariticform-row ariticform-text ariticform-field-1 ariticform-col-1-1 ariticform-double-col ariticform-required">
		                    <label id="ariticform_label_leadmagnetform_practice_name" for="ariticform_input_leadmagnetform_practice_name" class="ariticform-label">Practice name</label>
		                    <input id="ariticform_input_leadmagnetform_practice_name" name="ariticform[practice_name]" value="" class="ariticform-input" type="text">
		                    <span class="ariticform-errormsg" style="display: none;">This is required.</span>
		                </div>

		                <div id="ariticform_leadmagnetform_website" data-validate="website" data-validation-type="text" class="ariticform-row ariticform-text ariticform-field-2 ariticform-col-1-1 ariticform-double-col ariticform-required">
		                    <label id="ariticform_label_leadmagnetform_website" for="ariticform_input_leadmagnetform_website" class="ariticform-label">Website</label>
		                    <input id="ariticform_input_leadmagnetform_website" name="ariticform[website]" value="" class="ariticform-input" type="text">
		                    <span class="ariticform-errormsg" style="display: none;">This is required.</span>
		                </div>

		                <div id="ariticform_leadmagnetform_country" class="ariticform-row ariticform-select ariticform-field-3 ariticform-col-1-1 ariticform-double-col">
		                    <label id="ariticform_label_leadmagnetform_country" for="ariticform_input_leadmagnetform_country" class="ariticform-label">Country</label>
		                    <select id="ariticform_input_leadmagnetform_country" name="ariticform[country]" value="" class="ariticform-selectbox">
		                        <option value=""></option>
		                        <option value="Afghanistan">Afghanistan</option>
		                        <option value="Åland Islands">Åland Islands</option>
		                        <option value="Albania">Albania</option>
		                        <option value="Algeria">Algeria</option>
		                        <option value="Andorra">Andorra</option>
		                        <option value="Angola">Angola</option>
		                        <option value="Anguilla">Anguilla</option>
		                        <option value="Antarctica">Antarctica</option>
		                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
		                        <option value="Argentina">Argentina</option>
		                        <option value="Armenia">Armenia</option>
		                        <option value="Aruba">Aruba</option>
		                        <option value="Australia">Australia</option>
		                        <option value="Austria">Austria</option>
		                        <option value="Azerbaijan">Azerbaijan</option>
		                        <option value="Bahamas">Bahamas</option>
		                        <option value="Bahrain">Bahrain</option>
		                        <option value="Bangladesh">Bangladesh</option>
		                        <option value="Barbados">Barbados</option>
		                        <option value="Belarus">Belarus</option>
		                        <option value="Belgium">Belgium</option>
		                        <option value="Belize">Belize</option>
		                        <option value="Benin">Benin</option>
		                        <option value="Bermuda">Bermuda</option>
		                        <option value="Bhutan">Bhutan</option>
		                        <option value="Bolivia">Bolivia</option>
		                        <option value="Bonaire, Saint Eustatius and Saba">Bonaire, Saint Eustatius and Saba</option>
		                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
		                        <option value="Botswana">Botswana</option>
		                        <option value="Bouvet Island">Bouvet Island</option>
		                        <option value="Brazil">Brazil</option>
		                        <option value="Brunei">Brunei</option>
		                        <option value="Bulgaria">Bulgaria</option>
		                        <option value="Burkina Faso">Burkina Faso</option>
		                        <option value="Burundi">Burundi</option>
		                        <option value="Cape Verde">Cape Verde</option>
		                        <option value="Cambodia">Cambodia</option>
		                        <option value="Cameroon">Cameroon</option>
		                        <option value="Canada">Canada</option>
		                        <option value="Cayman Islands">Cayman Islands</option>
		                        <option value="Central African Republic">Central African Republic</option>
		                        <option value="Chad">Chad</option>
		                        <option value="Chile">Chile</option>
		                        <option value="China">China</option>
		                        <option value="Colombia">Colombia</option>
		                        <option value="Comoros">Comoros</option>
		                        <option value="Cook Islands">Cook Islands</option>
		                        <option value="Costa Rica">Costa Rica</option>
		                        <option value="Croatia">Croatia</option>
		                        <option value="Cuba">Cuba</option>
		                        <option value="Cyprus">Cyprus</option>
		                        <option value="Czech Republic">Czech Republic</option>
		                        <option value="Denmark">Denmark</option>
		                        <option value="Djibouti">Djibouti</option>
		                        <option value="Dominica">Dominica</option>
		                        <option value="Dominican Republic">Dominican Republic</option>
		                        <option value="Democratic Republic of the Congo">Democratic Republic of the Congo</option>
		                        <option value="East Timor">East Timor</option>
		                        <option value="Ecuador">Ecuador</option>
		                        <option value="Egypt">Egypt</option>
		                        <option value="El Salvador">El Salvador</option>
		                        <option value="Equatorial Guinea">Equatorial Guinea</option>
		                        <option value="Eritrea">Eritrea</option>
		                        <option value="Estonia">Estonia</option>
		                        <option value="Ethiopia">Ethiopia</option>
		                        <option value="Falkland Islands">Falkland Islands</option>
		                        <option value="Fiji">Fiji</option>
		                        <option value="Finland">Finland</option>
		                        <option value="France">France</option>
		                        <option value="French Guiana">French Guiana</option>
		                        <option value="French Polynesia">French Polynesia</option>
		                        <option value="Gabon">Gabon</option>
		                        <option value="Gambia">Gambia</option>
		                        <option value="Georgia">Georgia</option>
		                        <option value="Germany">Germany</option>
		                        <option value="Ghana">Ghana</option>
		                        <option value="Gibraltar">Gibraltar</option>
		                        <option value="Greece">Greece</option>
		                        <option value="Greenland">Greenland</option>
		                        <option value="Grenada">Grenada</option>
		                        <option value="Guadeloupe">Guadeloupe</option>
		                        <option value="Guam">Guam</option>
		                        <option value="Guatemala">Guatemala</option>
		                        <option value="Guernsey">Guernsey</option>
		                        <option value="Guinea">Guinea</option>
		                        <option value="Guinea Bissau">Guinea Bissau</option>
		                        <option value="Guyana">Guyana</option>
		                        <option value="Haiti">Haiti</option>
		                        <option value="Heard Island and McDonald Islands">Heard Island and McDonald Islands</option>
		                        <option value="Holy See">Holy See</option>
		                        <option value="Honduras">Honduras</option>
		                        <option value="Hong Kong">Hong Kong</option>
		                        <option value="Hungary">Hungary</option>
		                        <option value="Iceland">Iceland</option>
		                        <option value="India">India</option>
		                        <option value="Indonesia">Indonesia</option>
		                        <option value="Iran">Iran</option>
		                        <option value="Iraq">Iraq</option>
		                        <option value="Ireland">Ireland</option>
		                        <option value="Israel">Israel</option>
		                        <option value="Italy">Italy</option>
		                        <option value="Ivory Coast">Ivory Coast</option>
		                        <option value="Jamaica">Jamaica</option>
		                        <option value="Japan">Japan</option>
		                        <option value="Jersey">Jersey</option>
		                        <option value="Jordan">Jordan</option>
		                        <option value="Kazakhstan">Kazakhstan</option>
		                        <option value="Kenya">Kenya</option>
		                        <option value="Kiribati">Kiribati</option>
		                        <option value="Kuwait">Kuwait</option>
		                        <option value="Kyrgyzstan">Kyrgyzstan</option>
		                        <option value="Laos">Laos</option>
		                        <option value="Latvia">Latvia</option>
		                        <option value="Lebanon">Lebanon</option>
		                        <option value="Lesotho">Lesotho</option>
		                        <option value="Liberia">Liberia</option>
		                        <option value="Libya">Libya</option>
		                        <option value="Liechtenstein">Liechtenstein</option>
		                        <option value="Lithuania">Lithuania</option>
		                        <option value="Luxembourg">Luxembourg</option>
		                        <option value="Macao">Macao</option>
		                        <option value="Macedonia">Macedonia</option>
		                        <option value="Madagascar">Madagascar</option>
		                        <option value="Malawi">Malawi</option>
		                        <option value="Malaysia">Malaysia</option>
		                        <option value="Maldives">Maldives</option>
		                        <option value="Mali">Mali</option>
		                        <option value="Malta">Malta</option>
		                        <option value="Marshall Islands">Marshall Islands</option>
		                        <option value="Martinique">Martinique</option>
		                        <option value="Mauritania">Mauritania</option>
		                        <option value="Mauritius">Mauritius</option>
		                        <option value="Mayotte">Mayotte</option>
		                        <option value="Mexico">Mexico</option>
		                        <option value="Micronesia">Micronesia</option>
		                        <option value="Moldova">Moldova</option>
		                        <option value="Monaco">Monaco</option>
		                        <option value="Mongolia">Mongolia</option>
		                        <option value="Montenegro">Montenegro</option>
		                        <option value="Montserrat">Montserrat</option>
		                        <option value="Morocco">Morocco</option>
		                        <option value="Mozambique">Mozambique</option>
		                        <option value="Myanmar">Myanmar</option>
		                        <option value="Namibia">Namibia</option>
		                        <option value="Nauru">Nauru</option>
		                        <option value="Nepal">Nepal</option>
		                        <option value="Netherlands">Netherlands</option>
		                        <option value="New Caledonia">New Caledonia</option>
		                        <option value="New Zealand">New Zealand</option>
		                        <option value="Nicaragua">Nicaragua</option>
		                        <option value="Niger">Niger</option>
		                        <option value="Nigeria">Nigeria</option>
		                        <option value="Niue">Niue</option>
		                        <option value="North Korea">North Korea</option>
		                        <option value="Northern Mariana Islands">Northern Mariana Islands</option>
		                        <option value="Norway">Norway</option>
		                        <option value="Oman">Oman</option>
		                        <option value="Pakistan">Pakistan</option>
		                        <option value="Palau">Palau</option>
		                        <option value="Palestine">Palestine</option>
		                        <option value="Panama">Panama</option>
		                        <option value="Papua New Guinea">Papua New Guinea</option>
		                        <option value="Paraguay">Paraguay</option>
		                        <option value="Peru">Peru</option>
		                        <option value="Philippines">Philippines</option>
		                        <option value="Pitcairn">Pitcairn</option>
		                        <option value="Poland">Poland</option>
		                        <option value="Portugal">Portugal</option>
		                        <option value="Puerto Rico">Puerto Rico</option>
		                        <option value="Qatar">Qatar</option>
		                        <option value="Republic of the Congo">Republic of the Congo</option>
		                        <option value="Réunion">Réunion</option>
		                        <option value="Romania">Romania</option>
		                        <option value="Russia">Russia</option>
		                        <option value="Rwanda">Rwanda</option>
		                        <option value="Saint Barthelemy">Saint Barthelemy</option>
		                        <option value="Saint Helena, Ascension and Tristan da Cunha">Saint Helena, Ascension and Tristan da Cunha</option>
		                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
		                        <option value="Saint Lucia">Saint Lucia</option>
		                        <option value="Saint Martin">Saint Martin</option>
		                        <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
		                        <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
		                        <option value="Samoa">Samoa</option>
		                        <option value="San Marino">San Marino</option>
		                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
		                        <option value="Saudi Arabia">Saudi Arabia</option>
		                        <option value="Senegal">Senegal</option>
		                        <option value="Serbia">Serbia</option>
		                        <option value="Seychelles">Seychelles</option>
		                        <option value="Sierra Leone">Sierra Leone</option>
		                        <option value="Singapore">Singapore</option>
		                        <option value="Slovakia">Slovakia</option>
		                        <option value="Slovenia">Slovenia</option>
		                        <option value="Solomon Islands">Solomon Islands</option>
		                        <option value="Somalia">Somalia</option>
		                        <option value="South Africa">South Africa</option>
		                        <option value="South Georgia and the South Sandwich Islands">South Georgia and the South Sandwich Islands</option>
		                        <option value="South Korea">South Korea</option>
		                        <option value="South Sudan">South Sudan</option>
		                        <option value="Spain">Spain</option>
		                        <option value="Sri Lanka">Sri Lanka</option>
		                        <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
		                        <option value="Sudan">Sudan</option>
		                        <option value="Suriname">Suriname</option>
		                        <option value="Swaziland">Swaziland</option>
		                        <option value="Sweden">Sweden</option>
		                        <option value="Switzerland">Switzerland</option>
		                        <option value="Syria">Syria</option>
		                        <option value="Tahiti">Tahiti</option>
		                        <option value="Taiwan">Taiwan</option>
		                        <option value="Tajikistan">Tajikistan</option>
		                        <option value="Tanzania">Tanzania</option>
		                        <option value="Thailand">Thailand</option>
		                        <option value="Togo">Togo</option>
		                        <option value="Tokelau">Tokelau</option>
		                        <option value="Tonga">Tonga</option>
		                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
		                        <option value="Tunisia">Tunisia</option>
		                        <option value="Turkey">Turkey</option>
		                        <option value="Turkmenistan">Turkmenistan</option>
		                        <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
		                        <option value="Tuvalu">Tuvalu</option>
		                        <option value="United Kingdom">United Kingdom</option>
		                        <option value="United States">United States</option>
		                        <option value="Unknown">Unknown</option>
		                        <option value="Uganda">Uganda</option>
		                        <option value="Ukraine">Ukraine</option>
		                        <option value="United Arab Emirates">United Arab Emirates</option>
		                        <option value="Uruguay">Uruguay</option>
		                        <option value="Uzbekistan">Uzbekistan</option>
		                        <option value="Vanuatu">Vanuatu</option>
		                        <option value="Venezuela">Venezuela</option>
		                        <option value="Vietnam">Vietnam</option>
		                        <option value="Virgin Islands (British)">Virgin Islands (British)</option>
		                        <option value="Virgin Islands (U.S.)">Virgin Islands (U.S.)</option>
		                        <option value="Wallis and Futuna">Wallis and Futuna</option>
		                        <option value="Western Sahara">Western Sahara</option>
		                        <option value="Yemen">Yemen</option>
		                        <option value="Yugoslavia">Yugoslavia</option>
		                        <option value="Zambia">Zambia</option>
		                        <option value="Zimbabwe">Zimbabwe</option>
		                    </select>
		                    <span class="ariticform-errormsg" style="display: none;"></span>
		                </div>

		                <div id="ariticform_leadmagnetform_email" data-validate="email" data-validation-type="email" class="ariticform-row ariticform-email ariticform-field-4 ariticform-col-1-1 ariticform-double-col ariticform-required">
		                    <label id="ariticform_label_leadmagnetform_email" for="ariticform_input_leadmagnetform_email" class="ariticform-label">Email</label>
		                    <input id="ariticform_input_leadmagnetform_email" name="ariticform[email]" value="" class="ariticform-input" type="email">
		                    <span class="ariticform-errormsg" style="display: none;">This is required.</span>
		                </div>
		                <div id="ariticform_leadmagnetform_gdpr_checkbox" data-validate="gdpr_checkbox" data-validation-type="checkboxgrp" class="ariticform-row ariticform-checkboxgrp ariticform-field-5 ariticform-col-1-1 ariticform-double-col ariticform-required">
		                    <div class="ariticform-checkboxgrp-input">
		                        <label id="ariticform_checkboxgrp_label_gdpr_checkbox" for="ariticform_checkboxgrp_checkbox_gdpr_checkbox" style="width:100%;">
		                            <input name="ariticform[gdpr_checkbox][]" id="ariticform_checkboxgrp_checkbox_gdpr_checkbox" type="checkbox" value="2" style="float: left;margin-right: 10px;">
		                            <p>By submitting the form, you agree to our <a href="https://dentacoin.com/privacy-policy">PrivacyPolicy</a>.</p>
		                        </label>
		                    </div>
		                    <span class="ariticform-errormsg" style="display: none;">This is required.</span>
		                </div>
		                <div id="ariticform_leadmagnetform_submit" class="ariticform-row ariticform-button-wrapper ariticform-field-6 ariticform-col-1-1 ariticform-single-col">
		                    <button type="submit" name="ariticform[submit]" id="ariticform_input_leadmagnetform_submit" value="" class="ariticform-button btn btn-default">Submit</button>
		                </div>
		            </div>
		        </div>

		        <input type="hidden" name="ariticform[formId]" id="ariticform_leadmagnetform_id" value="13">
		        <input type="hidden" name="ariticform[return]" id="ariticform_leadmagnetform_return" value="">
		        <input type="hidden" name="ariticform[formName]" id="ariticform_leadmagnetform_name" value="leadmagnetform">
		    </form>
		</div>
	@endif

    <div class="testimonials-section">
    	<div class="container tac">
    		<h2>{!! nl2br(trans('trp.page.index-dentist.testimonial.title')) !!}</h2>
    		<span>{!! nl2br(trans('trp.page.index-dentist.testimonial.subtitle')) !!}</span>
    	</div>

    	@if($testimonials->isNotEmpty())
	    	<div class="container">
		    	<div class="flickity-testimonial">
		    		@foreach($testimonials as $testim)
			    		<div class="testimonial">
			    			<div class="testimonial-inner">
				    			<img src="{{ $testim->getImageUrl() }}">
				    			<h4>{!! nl2br($testim->description) !!}</h4>
				    			<p class="name">{!! nl2br($testim->name) !!}</p>
				    			<p>{!! nl2br($testim->job) !!}</p>
				    		</div>
			    		</div>
			    	@endforeach
		    	</div>
			</div>
		@endif

    </div>

    <div class="container section-how">

    	<h2 class="tac">
    		{!! nl2br(trans('trp.page.index-dentist.how-works-title')) !!}
    	</h2>

    	<div class="clearfix mobile-flickity">
    		<div class="left">
    			<div class="how-block flex flex-center">
	    			<span class="how-number">01</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-1', [
							'link' => '<a href="javascript:;" data-popup="popup-register">',
							'endlink' => '</a>',
						])) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">02</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-2')) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">03</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-3')) !!}
	    				
	    			</p>
	    		</div>
    		</div>
    		<div class="right">		    			
    			<div class="how-block flex flex-center">
	    			<span class="how-number">04</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-4', [
							'link' => '<a href="https://wallet.dentacoin.com/" target="_blank">',
							'endlink' => '</a>',
						])) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">05</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-5')) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">06</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-6')) !!}
	    				
	    			</p>
	    		</div>
    		</div>
    	</div>

    	<div class="tac">
    		<a href="javascript::" class="button button-sign-up-dentist" data-popup="popup-register">{!! nl2br(trans('trp.page.index-dentist.create-listing')) !!}</a>
    	</div>
    </div>

    <div class="section-learn">
    	<div class="container flex">
    		<div class="col">
    			<img src="{{ url('img-trp/dentacoin-patients-rely-on-only-reviews.png') }}" alt="Dentacoin patients rely on only reviews">
    		</div>
    		<div class="col">
	    		<h2>
	    			{!! nl2br(trans('trp.page.index-dentist.cta')) !!}
	    			
	    		</h2>
	    		<a href="javascript:;" class="button button-yellow button-sign-up-dentist" data-popup="popup-register">
	    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
	    		</a>
	    	</div>
    	</div>
    </div>


	@include('trp.popups.lead-magnet')
	
@endsection