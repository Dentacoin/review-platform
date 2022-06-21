<div class="popup no-image" id="popup-branch" scss-load="trp-popup-branch" js-load="branch">
	<div class="popup-inner">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
		</div>

		{!! Form::open([
			'method' => 'post', 
			'class' => 'add-new-branch-form', 
			'url' => getLangUrl('profile/add-new-branch'), 
		]) !!}
			{!! csrf_field() !!}

			<div id="branch-option-1" class="branch-content">

				<h2 class="mont">
					{{-- {{ trans('trp.popup.add-branch.title') }} --}}
					Add branch office
				</h2>

				<div class="modern-field alert-after">
					<input type="text" name="clinic_name" id="clinic_name" class="modern-input clinic_name" autocomplete="off">
					<label for="clinic_name">
						{{-- <span>{{ trans('trp.popup.add-branch.clinic-name') }}</span> --}}
						<span>Enter Clinic name:</span>
					</label>
					{{-- <p>{{ trans('trp.popup.add-branch.clinic-name.description') }}</p> --}}
					<p> Please, write the full name of the clinic to ensure that patients will find it easily.</p>
				</div>

				<div class="modern-field alert-after">
					<input type="text" name="clinic_name_alternative" id="clinic_name_alternative" class="modern-input clinic_name_alternative" autocomplete="off" >
					<label for="clinic_name_alternative">
						{{-- <span>{{ trans('trp.popup.add-branch.clinic-name-alternative') }}:</span> --}}
						<span>Enter Alternative spelling (optional):</span>
					</label>
					{{-- <p>{{ trans('trp.popup.add-branch.clinic-name-alternative.description') }}</p> --}}
					<p>For example, Дентална практика “ВитаДент”</p>
				</div>

				<div class="address-suggester-wrapper-input">
					<div class="modern-field alert-after">
			  			<select name="clinic_country_id" id="clinic_country_id" class="modern-input country-select">
			  				@if(!$country_id)
				  				<option>-</option>
				  			@endif
			  				@foreach( $countries as $country )
			  					<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
			  				@endforeach
			  			</select>
					</div>

					<div class="modern-field alert-after">
						<input type="text" name="clinic_address" id="clinic_address" class="modern-input clinic_address address-suggester-input" autocomplete="chrome-off">
						<label for="clinic_address">
							<span>{{ trans('trp.popup.add-branch.address') }}:</span>
						</label>
						{{-- <p>{{ trans('trp.popup.add-branch.address.description') }}</p> --}}
						<p>Please, enter the full address of your branch office as written on your website, Facebook or Google Business page.</p>
					</div>
                    <div class="suggester-map-div" style="height: 222px; display: none; margin: 10px 0px;"></div>
                    <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px 20px;">
						Please, check if we got the right address. If not, just drag the map to adjust it.
                    	{{-- {!! nl2br(trans('trp.common.check-address')) !!} --}}
                    </div>
                    <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: 10px 0px;">
                    	{!! nl2br(trans('trp.common.invalid-address')) !!}
                    </div>
                </div>

				<div class="tac">
					<a href="javascript:;" id="first-branch-next" class="blue-button next-branch-button" to-step="2" branch-url="{{ getLangUrl('profile/add-new-branch/1') }}" style="margin-right: auto;">{{ trans('trp.popup.add-branch.button-next') }}</a>
				</div>
			</div>

			<div id="branch-option-2" class="branch-content" style="display: none;">

				<h2 class="mont">
					{{-- {{ trans('trp.popup.add-branch.title') }} --}}
					Complete branch profile
				</h2>

				<div class="flex flex-mobile alert-after phone-code-holder-wrapper">
					<div>
	    				<span class="phone-code-holder">{{ $country_id ? '+'.$countries->where('id', $country_id)->first()->phone_code : '' }}</span>
					</div>
					<div style="flex: 1;" class="modern-field">
						<input type="text" name="clinic_phone" id="clinic_phone" class="modern-input clinic_phone" autocomplete="off"/>
						<label for="clinic_phone">
							{{-- <span>{{ trans('trp.popup.add-branch.phone') }}:</span> --}}
							<span>Enter phone number without country code:</span>
						</label>
					</div>
				</div>
				
                <div class="modern-field alert-after">
					<input type="text" name="clinic_website" id="clinic_website" class="modern-input clinic_website" autocomplete="off"/>
					<label for="clinic_website">
						{{-- <span>{{ trans('trp.popup.add-branch.website') }}</span> --}}
						<span>Enter full website URL:</span>
					</label>
					<p>{{ trans('trp.popup.add-branch.website.description') }}</p>
				</div>

				<div class="flex alert-after last-step-flex">
					<div class="col">
						<h4>Add branch profile image:</h4>
						<div class="upload-image-wrapper">
							<label for="add-avatar-clinic-branch" class="image-label">
								<div class="plus-image">
									<img src="{{ url('img-trp/add-icon.png') }}" class="tooltip-text" text="Required resolution: 200x200 px (max. 500x500 px) <br/> Max. image size: 2 MB"/>
									<span>
										Add image
										{{-- {!! nl2br(trans('trp.page.user.reviews-image')) !!} --}}
									</span>
								</div>
								<div class="loader">
									<i></i>
								</div>
								<input type="file" name="clinic_image" class="add-avatar-clinic-branch" id="add-avatar-clinic-branch" upload-url="{{ getLangUrl('register/upload') }}" accept="image/png,image/jpeg,image/jpg"/>
								<input type="hidden" name="avatar" class="avatar"/>
							</label>
		
							<div class="cropper-container"></div>
							<div class="avatar-name-wrapper">
								<span class="avatar-name"></span>
								<button class="destroy-croppie" type="button">×</button>
							</div>

							<div class="alert alert-warning image-big-error" style="display: none; margin-top: 20px;">The file you selected is large. Max size: 2MB.</div>
						</div>
					</div>
					<div class="col">
						<h4>Select branch’s specialties:</h4>
						
						<div class="specializations">

							@foreach($categories as $k => $v)
								<label class="checkbox-label" for="checkbox-{{ $k }}-branch">
									{{ $v }}
									<input 
										type="checkbox" 
										class="special-checkbox" 
										id="checkbox-{{ $k }}-branch" 
										name="clinic_specialization[]" 
										value="{{ $loop->index }}"
									>
								</label>
							@endforeach
                        </div>
					</div>
				</div>

				<div class="tac">
					<a href="javascript:;" class="white-button prev-branch-button" to-step="1"><</a>
					<button type="submit" class="blue-button submit-branch-button">
						<div class="loader"><i></i></div>
						{{ trans('trp.popup.add-branch.button-publish') }}
					</button>
				</div>
			</div>
		{!! Form::close() !!}
	</div>
</div>