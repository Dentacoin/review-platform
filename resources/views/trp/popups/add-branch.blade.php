<div class="popup fixed-popup" id="popup-branch">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
			{{ trans('trp.popup.add-branch.title') }}
		</h2>

		<div class="popup-tabs branch-tabs colorful-tabs flex flex-mobile">
			<a class="active col" href="javascript:;" data-branch="1" style="z-index: 3">
				1
			</a>
			<a class="col" href="javascript:;" data-branch="2" style="z-index: 2">
				2
			</a>
			<a class="col" href="javascript:;" data-branch="3" style="z-index: 1">
				3
			</a>
		</div>

		<div class="bottom-gray-border"></div>

		{!! Form::open(array('method' => 'post', 'class' => 'add-new-branch-form', 'url' => getLangUrl('profile/add-new-branch'), 'success-url' => getLangUrl('branches/'.$user->slug) )) !!}
			{!! csrf_field() !!}

			<div id="branch-option-1" class="branch-content">

				<div class="modern-field alert-after">
					<input type="text" name="clinic_name" id="clinic_name" class="modern-input tooltip-text input-tooltip clinic_name" text="{{ trans('trp.popup.add-branch.clinic-name.tooltip') }}" autocomplete="off">
					<label for="clinic_name">
						<span>{{ trans('trp.popup.add-branch.clinic-name') }}</span>
					</label>
					<p>{{ trans('trp.popup.add-branch.clinic-name.description') }}</p>
				</div>
				<div class="modern-field alert-after">
					<input type="text" name="clinic_name_alternative" id="clinic_name_alternative" class="modern-input clinic_name_alternative" autocomplete="off">
					<label for="clinic_name_alternative">
						<span>{{ trans('trp.popup.add-branch.clinic-name-alternative') }}:</span>
					</label>
					<p>{{ trans('trp.popup.add-branch.clinic-name-alternative.description') }}</p>
				</div>

				<div class="alert invite-alert" style="display: none; margin-top: 20px;">
				</div>

				<a href="javascript:;" id="first-branch-next" class="button next-branch-button" to-step="2" branch-url="{{ getLangUrl('profile/add-new-branch/1') }}">{{ trans('trp.popup.add-branch.button-next') }}</a>
			</div>

			<div id="branch-option-2" class="branch-content" style="display: none;">
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
						<input type="text" name="clinic_address" id="clinic_address" class="modern-input tooltip-text input-tooltip clinic_address address-suggester-input" autocomplete="chrome-off" text="{{ trans('trp.popup.add-branch.address.tooltip') }}">
						<label for="clinic_address">
							<span>{{ trans('trp.popup.add-branch.address') }}:</span>
						</label>
						<p>{{ trans('trp.popup.add-branch.address.description') }}</p>
					</div>
                    <div class="suggester-map-div" style="height: 100px; display: none; margin: 10px 0px;">
                    </div>
                    <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px 20px;">
                    	{!! nl2br(trans('trp.common.check-address')) !!}
                    </div>
                    <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: 10px 0px;">
                    	{!! nl2br(trans('trp.common.invalid-address')) !!}
                    </div>
                </div>

                <div class="modern-field alert-after">
					<input type="text" name="clinic_website" id="clinic_website" class="modern-input clinic_website" autocomplete="off">
					<label for="clinic_website">
						<span>{{ trans('trp.popup.add-branch.website') }}</span>
					</label>
					<p>{{ trans('trp.popup.add-branch.website.description') }}</p>
				</div>

				<div class="flex flex-mobile alert-after">
					<div>
	    				<span class="phone-code-holder">{{ $country_id ? '+'.$countries->where('id', $country_id)->first()->phone_code : '' }}</span>
					</div>
					<div style="flex: 1;" class="modern-field">
						<input type="text" name="clinic_phone" id="clinic_phone" class="modern-input clinic_phone" autocomplete="off">
						<label for="clinic_phone">
							<span>{{ trans('trp.popup.add-branch.phone') }}:</span>
						</label>
					</div>
				</div>

				<div class="flex flex-mobile">
					<div class="col">
						<a href="javascript:;" class="button prev-branch-button" to-step="1">{{ trans('trp.popup.add-branch.button-back') }}</a>
					</div>
					<div class="col">
						<a href="javascript:;" id="second-branch-next" class="button next-branch-button" to-step="3" branch-url="{{ getLangUrl('profile/add-new-branch/2') }}">{{ trans('trp.popup.add-branch.button-next') }}</a>
					</div>
				</div>
			</div>

			<div id="branch-option-3" class="branch-content" style="display: none;">

				<div class="flex flex-mobile alert-after last-step-flex">
					<div class="col">
						<label for="add-avatar-clinic-branch" class="image-label">
							<div class="centered-hack">
								<i class="fas fa-plus"></i>
								<p>
									{!! nl2br(trans('trp.popup.popup-register.add-photo')) !!}													
								</p>
							</div>
				    		<div class="loader">
				    			<i class="fas fa-circle-notch fa-spin"></i>
				    		</div>
							<input type="file" name="clinic_image" class="add-avatar-clinic-branch" id="add-avatar-clinic-branch" upload-url="{{ getLangUrl('register/upload') }}">
							
						</label>
						<input type="hidden" class="photo-name-branch" name="photo" >
						<input type="hidden" class="photo-thumb-branch" name="photo-thumb" >

						<div class="max-size-label">
							<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="width-100">
								<path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z" class=""></path>
							</svg>
							{{ trans('trp.popup.add-branch.image-max-size') }}
						</div>
					</div>
					<div class="col">
						<div class="specilializations">
							<p class="checkbox-question">
								{{ trans('trp.popup.add-branch.specializations') }}:
							</p>
					    	@foreach($categories as $k => $v)
								<label class="checkbox-label" for="checkbox-{{ $k }}-branch">
									<input 
										type="checkbox" 
										class="special-checkbox" 
										id="checkbox-{{ $k }}-branch" 
										name="clinic_specialization[]" 
										value="{{ $loop->index }}"
									>
									<i class="far fa-square"></i>
									{{ $v }}
								</label>
                            @endforeach
                        </div>

					</div>
				</div>

				<div class="flex flex-mobile">
					<div class="col">
						<a href="javascript:;" class="button prev-branch-button" to-step="2">{{ trans('trp.popup.add-branch.button-back') }}</a>
					</div>
					<div class="col">
						<input type="submit" class="button submit-branch-button" value="{{ trans('trp.popup.add-branch.button-publish') }}">
					</div>
				</div>
			</div>
		{!! Form::close() !!}
	</div>
</div>