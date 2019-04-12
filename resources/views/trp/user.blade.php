@extends('trp')

@section('content')


<div class="black-overflow" style="display: none;">
</div>
<div class="home-search-form">
	@include('trp.parts.search-form')
	
</div>

<div class="blue-background"></div>

<div class="container edit-profile-wrapper">
	<div class="profile-info-mobile">

		@if(!empty($user) && $user->id==$item->id)
			{!! Form::open(array('method' => 'post', 'class' => 'edit-profile', 'style' => 'display: none;', 'url' => getLangUrl('profile/info') )) !!}
				{!! csrf_field() !!}
				<div class="flex-mobile">
					<div class="flex-5">
						<label for="add-avatar-mobile" class="image-label" {!! $user->hasimage ? 'style="background-image: url('.$user->getImageUrl(true).')"' : '' !!}>
							<div class="centered-hack">
								<i class="fas fa-camera"></i>
								<p>
			    					@if( !$user->hasimage )
			    						{!! nl2br(trans('trp.page.user.add-photo')) !!}
						    			
				    				@else
				    					{!! nl2br(trans('trp.page.user.change-photo')) !!}
					    				
									@endif
					    		</p>
							</div>
				    		<div class="loader">
				    			<i class="fas fa-circle-notch fa-spin"></i>
				    		</div>
							<input type="file" name="image" id="add-avatar-mobile" upload-url="{{ getLangUrl('profile/info/upload') }}">
						</label>
					</div>
					<div class="flex-7">
						<a href="javascript:;" class="share-mobile" data-popup="popup-share">
							<i class="fas fa-share-alt"></i>
						</a>
						@if(!$user->is_clinic)
							{{ Form::select( 'title' , [
			                    '' => '-',
			                    'dr' => 'Dr.',
			                    'prof' => 'Prof. Dr.'
			                ] , $user->title , array('class' => 'input') ) }}
						@endif
						<input type="text" name="name" class="input dentist-name" placeholder="{!! nl2br(trans('trp.page.user.name')) !!}" value="{{ $user->name }}">
						<input type="text" name="name_alternative" class="input" placeholder="{!! nl2br(trans('trp.page.user.name_alterantive')) !!}" value="{{ $user->name_alternative }}">
					</div>		
				</div>
				<div class="profile-details address-suggester-wrapper">
                	<select class="input country-select" name="country_id">
                		<option value="">-</option>
                		@foreach(\App\Models\Country::get() as $country)
                			<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $user->country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
                		@endforeach
                	</select>
				    <div>
				    	<input type="text" name="address" class="input address-suggester" autocomplete="off" placeholder="{!! nl2br(trans('trp.page.user.city-street')) !!}" value="{{ $user->address }}">
                        <div class="suggester-map-div" {!! $user->lat ? 'lat="'.$user->lat.'" lon="'.$user->lon.'"' : '' !!} style="height: 100px; display: none; margin: 10px 0px;">
                        </div>
                        <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px;">
                        	{!! nl2br(trans('trp.common.check-address')) !!}
                        </div>
                        <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: 10px 0px;">
                        	{!! nl2br(trans('trp.common.invalid-address')) !!}
                        </div>
                    </div>
			    	<input type="text" name="open" class="input" placeholder="{!! nl2br(trans('trp.page.user.open-hours')) !!}" value="{{ strip_tags($user->getWorkHoursText()) }}" autocomplete="off" data-popup-logged="popup-wokring-time">
			    	<div class="flex phone-widget">
				    	<span class="phone-code-holder">{{ $user->country_id ? '+'.$user->country->phone_code : '' }}</span>
						<input type="tel" name="phone" class="input" placeholder="{!! nl2br(trans('trp.page.user.phone')) !!}" value="{{ $user->phone }}">
					</div>
			    	<input type="text" name="website" class="input" placeholder="{!! nl2br(trans('trp.page.user.website')) !!}" value="{{ $user->website }}">
			    	<input type="hidden" name="email" value="{{ $user->email }}">
			    	@if(!$user->is_clinic)
			    		<input type="text" name="open" class="input wokrplace-input" placeholder="{!! nl2br(trans('trp.page.user.my-workplace')) !!}" value="{{ strip_tags($user->getWorkplaceText(true)) }}" autocomplete="off" data-popup-logged="popup-wokrplace">
			    	@endif	
			    	<textarea class="input" name="short_description" placeholder="{!! nl2br(trans('trp.page.user.short-description')) !!}" maxlength="150">{{ $user->short_description }}</textarea>
			    	<div class="email-wrapper">
				    	<div class="flex flexed-wrap email-wrap">
				    		<div class="col social-networks">
				    			<a href="javascript:;" class="current-social">
			    					<i class="fas fa-envelope"></i>
			    				</a>
				    		</div>
				    		<div class="col">
				    			<input type="text" name="email_public" class="input social-link-input" placeholder="{!! nl2br(trans('trp.page.user.user-public-email')) !!}" value="{{ !empty($user->email_public) ? $user->email_public : $user->email }}" maxlength="100" {!! !empty($user->email_public) ? '' : 'disabled' !!}>
				    		</div>
				    	</div>
				    	<label class="checkbox-label label-public-email {!! !empty($user->email_public) ? '' : 'active' !!}" for="current-email-mobile"">
							<input type="checkbox" class="special-checkbox" id="current-email-mobile" cur-email="{{ $user->email }}" name="current-email" value="{!! !empty($user->email_public) ? '0' : '1' !!}" {!! !empty($user->email_public) ? '' : 'checked' !!} >
							<i class="far fa-square"></i>
							{!! nl2br(trans('trp.page.user.user-registration-email')) !!}
						</label>			    	
				    </div>
			    	
			    	<div class="s-wrap"> 
				    	@if(!empty($user->socials))
				    		@foreach($user->socials as $k => $v)
						    	<div class="flex social-wrap flexed-wrap">
						    		<div class="col social-networks">
						    			<a href="javascript:;" class="current-social" cur-type="{{ $k }}">
					    					<i class="{{ config('trp.social_network')[$k] }}"></i>
					    				</a>
						    			<div class="social-dropdown"> 
							    			@foreach(config('trp.social_network') as $key => $sn)
							    				<a href="javascript:;" social-type="{{ $key }}" social-class="{{ $sn }}" class="social-link {!! isset($user->socials[$key]) ? 'inactive' : ''; !!}">
							    					<i class="{{ $sn }}" class-attr="{{ $sn }}"></i>
							    				</a>
							    			@endforeach
							    		</div>
						    		</div>
						    		<div class="col">
						    			<input type="text" name="socials[{{ $k }}]" class="input social-link-input" value="{{ $v }}" maxlength="300">
						    		</div>
						    	</div>
						    @endforeach
					    @else
					    	<div class="flex social-wrap flexed-wrap">
					    		<div class="col social-networks">
					    			<a href="javascript:;" class="current-social" cur-type="{{ array_values(config('trp.social_network'))[0] }}">
				    					<i class="{{ array_values(config('trp.social_network'))[0] }}"></i>
				    				</a>
					    			<div class="social-dropdown"> 
						    			@foreach(config('trp.social_network') as $key => $sn)
						    				<a href="javascript:;" social-type="{{ $key }}" social-class="{{ $sn }}" class="social-link {!! $loop->first ? 'inactive' : '' !!}">
						    					<i class="{{ $sn }}" class-attr="{{ $sn }}"></i>
						    				</a>
						    			@endforeach
						    		</div>
					    		</div>
					    		<div class="col">
					    			<input type="text" name="socials[{{ key(config('trp.social_network')) }}]" class="input social-link-input" maxlength="300">
					    		</div>
					    	</div>
					    @endif
					</div>
				    
				    @if(empty($user->socials) || (!empty($user->socials) && (count($user->socials) != count(config('trp.social_network')))))
			    		<a href="javascript:;" class="add-social-profile">{!! nl2br(trans('trp.page.user.add-social-profile')) !!}</a>
			    	@endif
				</div>
				<div class="edit-buttons">
					<button class="button" type="submit">
						{!! nl2br(trans('trp.page.user.save')) !!}
					</button>
					<a href="javascript:;" class="cancel-edit open-edit">
						{!! nl2br(trans('trp.page.user.cancel')) !!}
					</a>
				</div>
				<input type="hidden" name="json" value="1">
				<div class="edit-error alert alert-warning" style="display: none;">
				</div>
			{!! Form::close() !!}
		@endif



		<div class="view-profile">
			<div class="flex-mobile">
				<div class="flex-5">
					<div class="avatar cover" style="background-image: url('{{ $item->getImageUrl(true) }}');"> </div>
				</div>
				<div class="flex-7">
					<a href="javascript:;" class="share-mobile" data-popup="popup-share">
						<i class="fas fa-share-alt"></i>
					</a>
					<h3>
						{{ $item->getName() }}
					</h3>
					@if( $item->name_alternative )
						<p class="alternative-name">({{ $item->name_alternative }})</p>
					@endif
					<span class="type">
						@if($item->is_partner)
		    				<div class="img">
								<img class="black-filter" src="{{ url('img-trp/mini-logo.png') }}">
							</div>
							<span>
								{!! nl2br(trans('trp.page.user.partner')) !!}
							</span> 
						@endif
						{{ $item->is_clinic ? 'Clinic' : 'Dentist' }}
					</span>
					@if(!empty($user) && $user->id==$item->id)
						<a class="edit-button open-edit" href="javascript:;">
							<i class="fas fa-edit"></i>
							{!! nl2br(trans('trp.page.user.edit-profile')) !!}
							
						</a>
					@endif
				</div>		
			</div>
			<div class="profile-details">
				<div class="p">
		    		<div class="img">
						<img class="black-filter" src="{{ url('img-trp/map-pin.png') }}">
					</div>
					{{ $item->city_name ? $item->city_name.', ' : '' }}
					{{ $item->state_name ? $item->state_name.', ' : '' }} 
					{{ $item->country->name }} 
					<!-- <span class="gray-text">(2 km away)</span> -->
				</div>
		    	@if( $time = $item->getWorkHoursText() )
		    		<div class="p">
			    		<div class="img">
			    			<img class="black-filter" src="{{ url('img-trp/open.png') }}">
			    		</div>
		    			{!! $time !!}
		    		</div>
		    	@endif
		    	@if( $item->phone )
		    	<a href="tel:{{ $item->getFormattedPhone(true) }}" class="p">
		    		<div class="img">
		    			<img class="black-filter" src="{{ url('img-trp/phone.png') }}">
		    		</div>
		    		{{ $item->getFormattedPhone() }}
		    	</a>
		    	@endif
		    	@if( $item->website )
		    	<a href="{{ $item->getWebsiteUrl() }}" target="_blank" class="p">
		    		<div class="img">
		    			<img class="black-filter" src="{{ url('img-trp/site.png') }}">
		    		</div>
			    	{{ $item->website }}
		    	</a>
		    	@endif

		    	@if( $workplace = $item->getWorkplaceText( !empty($user) && $user->id==$item->id ) )
		    		<div class="p">
			    		<div class="img">
			    			<img class="black-filter" src="{{ url('img-trp/clinic.png') }}">
			    		</div>
		    			{!! $workplace !!}
		    		</div>
		    	@endif
			    <div class="p profile-socials">
		    		<a class="social" href="mailto:{{ $item->email_public ? $item->email_public : $item->email }}">
		    			<i class="fas fa-envelope"></i>
		    		</a>
		    		@if( $item->socials )
			    		@foreach($item->socials as $k => $v)
				    		<a class="social" href="{{ $v }}" target="_blank">
				    			<i class="{{ config('trp.social_network')[$k] }}"></i>
				    		</a>
				    	@endforeach
				    @endif
			    </div>		    	
			</div>
			<div class="profile-rating col tac">
				<div class="ratings average">
					<div class="stars">
						<div class="bar" style="width: {{ $item->avg_rating/5*100 }}%;">
						</div>
					</div>
				</div>

				<div class="rating">
					({{ intval($item->ratings) }} reviews)
				</div>

				@if(!empty($user) && $user->id==$item->id)
					<a href="javascript:;" class="button" data-popup-logged="popup-invite">
						{!! nl2br(trans('trp.page.user.invite')) !!}
						
					</a>
					<a href="javascript:;" class="button button-inner-white" data-popup-logged="popup-widget" style="text-transform: initial;">
						{!! nl2br(trans('trp.page.user.widget')) !!}
						
					</a>
				@elseif( empty($user) || !$user->is_dentist )
					<a href="javascript:;" class="button" data-popup-logged="submit-review-popup">
						{!! nl2br(trans('trp.page.user.submit-review')) !!}
						
					</a>
					@if(!$isTrusted && !$has_asked_dentist)
						<a href="javascript:;" class="button button-inner-white button-ask" data-popup-logged="popup-ask-dentist">
							{!! nl2br(trans('trp.page.user.request-invite')) !!}
							
						</a>
					@endif
				@endif
			</div>
		</div>
		@if(!empty($item->short_description))
			<a href="javascript:;" class="short-desc-arrow"></a>
		@endif
	</div>

	<div class="information flex">
    	<div class="profile-info col">

			<a href="javascript:;" class="share-button" data-popup="popup-share">
				<img src="img-trp/share.png"> {!! nl2br(trans('trp.common.share')) !!}
			</a>

			@if(!empty($user) && $user->id==$item->id)
				{!! Form::open(array('method' => 'post', 'class' => 'edit-profile clearfix', 'style' => 'display: none;', 'url' => getLangUrl('profile/info') )) !!}
					{!! csrf_field() !!}
					<label for="add-avatar" class="image-label" {!! $user->hasimage ? 'style="background-image: url('.$user->getImageUrl(true).')"' : '' !!}>
							<div class="centered-hack">
				    			<i class="fas fa-camera"></i>
								<p>
			    					@if( !$user->hasimage )
			    						{!! nl2br(trans('trp.page.user.add-photo')) !!}
				    				@else
			    						{!! nl2br(trans('trp.page.user.change-photo')) !!}
									@endif
					    		</p>
							</div>
			    		<div class="loader">
			    			<i class="fas fa-circle-notch fa-spin"></i>
			    		</div>
						<input type="file" name="image" id="add-avatar" upload-url="{{ getLangUrl('profile/info/upload') }}">
					</label>

					<div class="media-right address-suggester-wrapper">
						@if(!$user->is_clinic)
							<div class="flex">
								{{ Form::select( 'title' , [
				                    '' => '-',
				                    'dr' => 'Dr.',
				                    'prof' => 'Prof. Dr.'
				                ] , $user->title , array('class' => 'input') ) }}
								<input type="text" name="name" class="input dentist-name" placeholder="{!! nl2br(trans('trp.page.user.name')) !!}" value="{{ $user->name }}">
							</div>
						@else
							<input type="text" name="name" class="input dentist-name" placeholder="{!! nl2br(trans('trp.page.user.name')) !!}" value="{{ $user->name }}">
						@endif
						<input type="text" name="name_alternative" class="input" placeholder="{!! nl2br(trans('trp.page.user.name_alterantive')) !!}" value="{{ $user->name_alternative }}">
						<select class="input country-select" name="country_id">
	                		<option value="">-</option>
	                		@foreach(\App\Models\Country::get() as $country)
	                			<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $user->country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
	                		@endforeach
	                	</select>
	                	<div>
					    	<input type="text" name="address" class="input address-suggester" autocomplete="off" placeholder="{!! nl2br(trans('trp.page.user.city-street')) !!}" value="{{ $user->address }}">
	                        <div class="suggester-map-div" {!! $user->lat ? 'lat="'.$user->lat.'" lon="'.$user->lon.'"' : '' !!} style="height: 100px; display: none; margin: 10px 0px;">
	                        </div>
	                        <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px;">
	                        	{!! nl2br(trans('trp.common.check-address')) !!}
	                        </div>
	                        <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: 10px 0px;">
	                        	{!! nl2br(trans('trp.common.invalid-address')) !!}
	                        </div>
	                    </div>
				    	<input type="text" name="open" class="input" placeholder="{!! nl2br(trans('trp.page.user.open-hours')) !!}" value="{{ strip_tags($user->getWorkHoursText()) }}" autocomplete="off" data-popup-logged="popup-wokring-time">
				    	<div class="flex phone-widget">
					    	<span class="phone-code-holder">{{ $user->country_id ? '+'.$user->country->phone_code : '' }}</span>
							<input type="tel" name="phone" class="input" placeholder="{!! nl2br(trans('trp.page.user.phone')) !!}" value="{{ $user->phone }}">
						</div>
				    	<input type="text" name="website" class="input" placeholder="{!! nl2br(trans('trp.page.user.website')) !!}" value="{{ $user->website }}">
				    	<input type="hidden" name="email" value="{{ $user->email }}">
				    	@if(!$user->is_clinic)
					    	<input type="text" name="open" class="input wokrplace-input" placeholder="{!! nl2br(trans('trp.page.user.my-workplace')) !!}" value="{{ strip_tags($user->getWorkplaceText(true)) }}" autocomplete="off" data-popup-logged="popup-wokrplace">
				    	@endif
				    	<div class="email-wrapper">
					    	<div class="flex flexed-wrap email-wrap">
					    		<div class="col social-networks">
					    			<a href="javascript:;" class="current-social">
				    					<i class="fas fa-envelope"></i>
				    				</a>
					    		</div>
					    		<div class="col">
					    			<input type="text" name="email_public" class="input social-link-input" value="{{ !empty($user->email_public) ? $user->email_public : $user->email }}" placeholder="{!! nl2br(trans('trp.page.user.user-public-email')) !!}" maxlength="100" {!! !empty($user->email_public) ? '' : 'disabled' !!}>
					    		</div>
					    	</div>
					    	<label class="checkbox-label label-public-email {!! !empty($user->email_public) ? '' : 'active' !!}" for="current-email"">
								<input type="checkbox" class="special-checkbox" id="current-email" cur-email="{{ $user->email }}" name="current-email" value="{!! !empty($user->email_public) ? '0' : '1' !!}" {!! !empty($user->email_public) ? '' : 'checked' !!} >
								<i class="far fa-square"></i>
								{!! nl2br(trans('trp.page.user.user-registration-email')) !!}
							</label>			    	
					    </div>
					    <div class="s-wrap">
					    	@if(!empty($user->socials))
					    		@foreach($user->socials as $k => $v)
							    	<div class="flex social-wrap flexed-wrap">
							    		<div class="col social-networks">
							    			<a href="javascript:;" class="current-social" cur-type="{{ $k }}">
						    					<i class="{{ config('trp.social_network')[$k] }}"></i>
						    				</a>
							    			<div class="social-dropdown"> 
								    			@foreach(config('trp.social_network') as $key => $sn)
								    				<a href="javascript:;" social-type="{{ $key }}" social-class="{{ $sn }}" class="social-link {!! isset($user->socials[$key]) ? 'inactive' : ''; !!}">
								    					<i class="{{ $sn }}" class-attr="{{ $sn }}"></i>
								    				</a>
								    			@endforeach
								    		</div>
							    		</div>
							    		<div class="col">
							    			<input type="text" name="socials[{{ $k }}]" class="input social-link-input" value="{{ $v }}" maxlength="300">
							    		</div>
							    	</div>
							    @endforeach
						    @else
						    	<div class="flex social-wrap flexed-wrap">
						    		<div class="col social-networks">
						    			<a href="javascript:;" class="current-social" cur-type="{{ array_values(config('trp.social_network'))[0] }}">
					    					<i class="{{ array_values(config('trp.social_network'))[0] }}"></i>
					    				</a>
						    			<div class="social-dropdown"> 
							    			@foreach(config('trp.social_network') as $key => $sn)
							    				<a href="javascript:;" social-type="{{ $key }}" social-class="{{ $sn }}" class="social-link {!! $loop->first ? 'inactive' : '' !!}">
							    					<i class="{{ $sn }}" class-attr="{{ $sn }}"></i>
							    				</a>
							    			@endforeach
							    		</div>
						    		</div>
						    		<div class="col">
						    			<input type="text" name="socials[{{ key(config('trp.social_network')) }}]" class="input social-link-input" maxlength="300">
						    		</div>
						    	</div>
						    @endif
						</div>
					    
					    @if(empty($user->socials) || (!empty($user->socials) && (count($user->socials) != count(config('trp.social_network')))))
				    		<a href="javascript:;" class="add-social-profile">{!! nl2br(trans('trp.page.user.add-social-profile')) !!}</a>
				    	@endif
					</div>
					<div class="clearfix">
						<div class="clear flex flex-bottom">
							<div class="edit-short-description">
								<textarea class="input" name="short_description" placeholder="{!! nl2br(trans('trp.page.user.short-description')) !!}" maxlength="150">{{ $user->short_description }}</textarea>
							</div>
							<div class="edit-buttons">
								<button class="button" type="submit">
									{!! nl2br(trans('trp.page.user.save')) !!}
								</button>
								<a href="javascript:;" class="cancel-edit open-edit">
									{!! nl2br(trans('trp.page.user.cancel')) !!}
								</a>
							</div>
						</div>
					</div>
					<div class="edit-error alert alert-warning" style="display: none;">
					</div>
					<input type="hidden" name="json" value="1">
				{!! Form::close() !!}
			@endif

    		<div class="view-profile clearfix">
				<div class="avatar" style="background-image: url('{{ $item->getImageUrl(true) }}');"> </div>
				<div class="media-right">
					<h3>
						{{ $item->getName() }}
					</h3>
					@if( $item->name_alternative )
						<p class="alternative-name">({{ $item->name_alternative }})</p>
					@endif
					<div class="p">
						<div class="img">
							<img class="black-filter" src="{{ url('img-trp/map-pin.png') }}">
						</div>
						{{ $item->city_name ? $item->city_name.', ' : '' }}
						{{ $item->state_name ? $item->state_name.', ' : '' }} 
						{{ $item->country->name }} 
						<!-- <span class="gray-text">(2 km away)</span> -->
					</div>
			    	@if( $time = $item->getWorkHoursText() )
			    		<div class="p">
			    			<div class="img">
				    			<img class="black-filter" src="{{ url('img-trp/open.png') }}">
				    		</div>
			    			{!! $time !!}
			    		</div>
			    	@endif
			    	@if( $item->phone )
			    		<a class="p" href="tel:{{ $item->getFormattedPhone(true) }}">
			    			<div class="img">
			    				<img class="black-filter" src="{{ url('img-trp/phone.png') }}">
			    			</div>
			    			{{ $item->getFormattedPhone() }}
			    		</a>
			    	@endif
			    	@if( $item->website )
			    		<a class="p" href="{{ $item->getWebsiteUrl() }}" target="_blank">
			    			<div class="img">
			    				<img class="black-filter" src="{{ url('img-trp/site.png') }}">
			    			</div>
			    			{{ $item->website }}
			    		</a>
			    	@endif
			    	@if( $workplace = $item->getWorkplaceText( !empty($user) && $user->id==$item->id ) )
			    		<div class="p">
				    		<div class="img">
				    			<img class="black-filter" src="{{ url('img-trp/clinic.png') }}">
				    		</div>
			    			{!! $workplace !!}
			    		</div>
			    	@endif
				    <div class="p profile-socials">
				    	<a class="social" href="mailto:{{ $item->email_public ? $item->email_public : $item->email }}">
			    			<i class="fas fa-envelope"></i>
			    		</a>
				    	@if( $item->socials )
				    		@foreach($item->socials as $k => $v)
					    		<a class="social" href="{{ $v }}" target="_blank">
					    			<i class="{{ config('trp.social_network')[$k] }}"></i>
					    		</a>
					    	@endforeach
				    	@endif
				    </div>
				</div>
				<span class="type">
					@if($item->is_partner)
		    			<div class="img">
							<img class="black-filter white-logo" src="{{ url('img-trp/mini-logo-white.png') }}">
							<img class="black-filter black-logo" src="{{ url('img-trp/mini-logo.png') }}">
						</div>
						<span> {!! nl2br(trans('trp.page.user.partner')) !!}</span> 
					@endif
					{{ $item->is_clinic ? 'Clinic' : 'Dentist' }}
				</span>
			</div>
			@if(!empty($user) && $user->id==$item->id)
				<a class="edit-button open-edit" href="javascript:;">
					<i class="fas fa-edit"></i>
					{!! nl2br(trans('trp.page.user.edit-profile')) !!}
				</a>
			@endif
			@if(!empty($item->short_description))
				<a href="javascript:;" class="short-desc-arrow"></a>
			@endif

		</div>

		<div class="profile-rating col tac">
			<div class="ratings big">
				<div class="stars">
					<div class="bar" style="width: {{ $item->avg_rating/5*100 }}%;">
					</div>
				</div>
			</div>

			<div class="rating">
				({{ intval($item->ratings) }} reviews)
			</div>

			@if(!empty($user) && $user->id==$item->id)
				<a href="javascript:;" class="button" data-popup-logged="popup-invite">
					{!! nl2br(trans('trp.page.user.invite')) !!}
				</a>
				<a href="javascript:;" class="button button-inner-white" data-popup-logged="popup-widget" style="text-transform: initial;">
					{!! nl2br(trans('trp.page.user.widget')) !!}
				</a>
			@elseif( empty($user) || !$user->is_dentist )
				<a href="javascript:;" class="button" data-popup-logged="submit-review-popup">
					{!! nl2br(trans('trp.page.user.submit-review')) !!}
				</a>
				@if(!$isTrusted && !$has_asked_dentist)
					<a href="javascript:;" class="button button-inner-white button-ask" data-popup-logged="popup-ask-dentist">
						{!! nl2br(trans('trp.page.user.request-invite')) !!}
					</a>
				@endif
			@endif							

		</div>
    </div>

	@if(!empty($item->short_description))
		<div class="dentist-short-desc">
			{{ $item->short_description }}
		</div>
	@endif

    <div class="profile-tabs">
    	@if( $item->reviews_in_standard()->count() )
	    	<a class="tab" data-tab="reviews" href="javascript:;">
	    		{!! nl2br(trans('trp.page.user.reviews')) !!}
	    		
	    		({{ $item->reviews_in_standard()->count() }})
	    	</a>
    	@endif
    	@if( $item->reviews_in_video()->count() )
	    	<a class="tab" data-tab="videos" href="javascript:;">
	    		{!! nl2br(trans('trp.page.user.videos')) !!}
	    		
	    		({{ $item->reviews_in_video()->count() }})
	    	</a>
    	@endif
    	<a class="tab" data-tab="about" href="javascript:;">
    		{!! nl2br(trans('trp.page.user.about')) !!}
    		
    	</a>
    </div>

    <div class="details-wrapper profile-reviews-space">
    	@if($item->reviews_in_standard()->isNotEmpty() )
	    	<div class="tab-container" id="reviews">
	    		<h2 class="black-left-line section-title">
	    			{!! nl2br(trans('trp.page.user.reviews')) !!}
	    		</h2>
				@foreach($item->reviews_in_standard() as $review)

			    	<div class="review review-wrapper" review-id="{{ $review->id }}">
						<div class="review-header">
			    			<div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
			    			<span class="review-name">{{ $review->user->name }}: </span>
							@if($review->verified)
				    			<div class="trusted-sticker mobile-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $item->getName() ])) !!}">
				    				{!! nl2br(trans('trp.common.trusted')) !!}
				    				<i class="fas fa-info-circle"></i>
				    			</div>
			    			@endif
			    			@if($review->title)
			    			<span class="review-title">
			    				“{{ $review->title }}”
			    			</span>
			    			@endif
							@if($review->verified)

				    			<div class="trusted-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $item->getName() ])) !!}">
				    				{!! nl2br(trans('trp.common.trusted')) !!}
				    				<i class="fas fa-info-circle"></i>
				    			</div>
			    			@endif
		    			</div>
		    			<div class="review-rating">
		    				<div class="ratings">
								<div class="stars">
									<div class="bar" style="width: {{ $review->rating/5*100 }}%;">
									</div>
								</div>
								<span class="rating">
									({{ $review->rating }})
								</span>
							</div>
							<span class="review-date">
								{{ $review->created_at ? $review->created_at->toFormattedDateString() : '-' }}
							</span>
						</div>
						<div class="review-content">
							{!! nl2br($review->answer) !!}
							<a href="javascript:;" class="more">
								{!! nl2br(trans('trp.page.user.show-entire')) !!}
								
							</a>
						</div>

						<div class="review-footer flex flex-mobile break-mobile">

							@if($review->reply)
								<a class="reply-button show-hide" href="javascript:;" alternative="▾ Show replies" >
									▴ {!! nl2br(trans('trp.page.user.hire-replies')) !!}
									
								</a>
							@endif
							<div class="col">
								@if(!$review->reply && !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) )
									<a class="reply-review" href="javascript:;">
										<span>
											{!! nl2br(trans('trp.page.user.reply')) !!}
											
										</span>
									</a>
								@endif
								
								<a class="thumbs-up {!! ($my_upvotes && in_array($review->id, $my_upvotes) ) ? 'voted' : '' !!}" href="javascript:;">
									<img src="{{ url('img-trp/thumbs-up'.(($my_upvotes && in_array($review->id, $my_upvotes)) ? '-color' : '').'.png') }}">
									<span>
										{{ intval($review->upvotes) }}
									</span>
								</a>
								<a class="thumbs-down {!! ($my_downvotes && in_array($review->id, $my_downvotes)) ? 'voted' : '' !!}" href="javascript:;">
									<img src="{{ url('img-trp/thumbs-down'.(($my_downvotes && in_array($review->id, $my_downvotes)) ? '-color' : '').'.png') }}">
									<span>
										{{ intval($review->downvotes) }}
									</span>
								</a>

								<a class="share-review" href="javascript:;" data-popup="popup-share" share-href="{{ $item->getLink() }}?review_id={{ $review->id }}">
									<img src="{{ url('img-trp/share-review.png') }}">
									<span>
										{!! nl2br(trans('trp.common.share')) !!}
									</span>
								</a>
							</div>
						</div>

						@if(!$review->reply && !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) )
							<div class="review-replied-wrapper reply-form" style="display: none;">
								<div class="review">
				    				<div class="review-header">
						    			<div class="review-avatar" style="background-image: url('{{ $item->getImageUrl(true) }}');"></div>
						    			<span class="review-name">{{ $item->getName() }}</span>
					    			</div>
									<div class="review-content">
										<form method="post" action="{{ $item->getLink() }}reply/{{ $review->id }}" class="reply-form-element">
											{!! csrf_field() !!}
											<textarea class="input" name="reply" placeholder="{!! nl2br(trans('trp.page.user.reply-enter')) !!}"></textarea>
											<button class="button" type="submit" name="">{!! nl2br(trans('trp.page.user.reply-submit')) !!}</button>
											<div class="alert alert-warning" style="display: none;">
												{!! nl2br(trans('trp.page.user.reply-error')) !!}
												
											</div>
										</form>
									</div>
								</div>
							</div>
						@elseif($review->reply)
							<div class="review-replied-wrapper">
								<div class="review">
				    				<div class="review-header">
						    			<div class="review-avatar" style="background-image: url('{{ $item->getImageUrl(true) }}');"></div>
						    			<span class="review-name">{{ $item->getName() }}</span>
						    			<span class="review-date">
											{{ $review->replied_at ? $review->replied_at->toFormattedDateString() : '-' }}
										</span>
					    			</div>
									<div class="review-content">
										{!! nl2br($review->reply) !!}
									</div>

									<div class="review-footer">
										<div class="col">
											<a class="thumbs-up {!! ($my_upvotes && in_array($review->id, $my_upvotes) ) ? 'voted' : '' !!}" href="javascript:;">
												<img src="{{ url('img-trp/thumbs-up'.(($my_upvotes && in_array($review->id, $my_upvotes)) ? '-color' : '').'.png') }}">
												<span>
													{{ intval($review->upvotes_reply) }}
												</span>
											</a>
											<a class="thumbs-down {!! ($my_downvotes && in_array($review->id, $my_downvotes) ) ? 'voted' : '' !!}" href="javascript:;">
												<img src="{{ url('img-trp/thumbs-down'.(($my_downvotes && in_array($review->id, $my_downvotes)) ? '-color' : '').'.png') }}">
												<span>
													{{ intval($review->downvotes_reply) }}
												</span>
											</a>
										</div>
									</div>
								</div>
							</div>
						@endif
		    		</div>
		    	@endforeach
	    	</div>
		@endif

    	@if( $item->reviews_in_video()->count() )
	    	<div class="tab-container" id="videos">
	    		<h2 class="black-left-line section-title">
	    			{!! nl2br(trans('trp.page.user.reviews-video')) !!}
	    			
	    		</h2>

	    		<div class="video-review-container clearfix">
					@foreach($item->reviews_in_video() as $review)
		    			<div class="video-review more review-wrapper" review-id="{{ $review->id }}">
		    				<div class="video-image cover" style="background-image: url('https://img.youtube.com/vi/{{ $review->youtube_id }}/hqdefault.jpg');"></div>
		    				<div class="video-review-title">
		    					“{{ $review->title }}”
		    				</div>
		    				<div>
			    				<div class="ratings">
									<div class="stars">
										<div class="bar" style="width: {{ $review->rating/5*100 }}%;">
										</div>
									</div>
									<span class="rating">
										({{ $review->rating }})
									</span>
								</div>
								@if($review->verified)
									<div class="trusted-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $item->getName() ])) !!}">
					    				{!! nl2br(trans('trp.common.trusted')) !!}
				    					<i class="fas fa-info-circle"></i>
					    			</div>
				    			@endif
				    		</div>
				    		<div>
					    		<div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
				    			<span class="review-date">{{ $review->user->name }}, {{ $review->created_at ? $review->created_at->toFormattedDateString() : '-' }} </span>
				    		</div>
		    			</div>
		    		@endforeach
	    		</div>
	    	</div>
    	@endif

    	<div class="tab-container" id="about">
    		<h2 class="black-left-line section-title">
    			{!! nl2br(trans('trp.page.user.about-who',[
    				'name' => $item->getName()
    			])) !!}
    			
    		</h2>

    		<div class="about-container">
				@if($item->categories->isNotEmpty() || (!empty($user) && $item->id==$user->id))
	    			<div class="specialization" role="presenter">
						<img src="{{ url('img-trp/graduate-hat.png') }}">

		    			<span class="value-here" empty-value="{{ nl2br(trans('trp.page.user.specialty-empty')) }}">
		    				{{ $item->categories->isNotEmpty() ? implode(', ', $item->parseCategories($categories)) : nl2br(trans('trp.page.user.specialty-empty')) }}
	    				</span>
	    				@if(!empty($user) && $item->id==$user->id)
	    					<a>
	    						<img src="{{ url('img-trp/pencil.png') }}">
	    					</a>
	    				@endif
	    			</div>
	    			@if(!empty($user) && $item->id==$user->id)
		    			<div class="specialization" role="editor" style="display: none;">
							{{ Form::open(array('class' => 'edit-description', 'method' => 'post', 'url' => getLangUrl('profile/info') )) }}
								{!! csrf_field() !!}
								@foreach($categories as $k => $v)
									<label class="checkbox-label {!! in_array($loop->index, $user->categories->pluck('category_id')->toArray()) ? 'active' : '' !!}" for="checkbox-{{ $k }}" >
										<input type="checkbox" class="special-checkbox" id="checkbox-{{ $k }}" name="specialization[]" value="{{ $loop->index }}" {!! in_array($loop->index, $user->categories->pluck('category_id')->toArray()) ? 'checked="checked"' : '' !!}>
										<i class="far fa-square"></i>
										{{ $v }}
									</label>
                                @endforeach
                                <br/>
                                <input type="hidden" name="field" value="specialization" />
                                <input type="hidden" name="json" value="1" />
								<button type="submit" class="button">
									{!! nl2br(trans('trp.page.user.save')) !!}
								</button>
								<div class="alert alert-warning" style="display: none;">
								</div>
							{!! Form::close() !!}
		    			</div>
	    			@endif
				@endif
				@if(!empty($item->accepted_payment) || (!empty($user) && $item->id==$user->id))
	    			<div class="dentist-payments" role="presenter">
						<i class="fas fa-dollar-sign img"></i>
		    			<span class="value-here" empty-value="{{ nl2br(trans('trp.page.user.accepted-payment-empty')) }}">
		    				{{ $item->accepted_payment ? $item->parseAcceptedPayment( $item->accepted_payment ) : nl2br(trans('trp.page.user.accepted-payment-empty')) }}
	    				</span>
	    				@if(!empty($user) && $item->id==$user->id)
	    					<a>
	    						<img src="{{ url('img-trp/pencil.png') }}">
	    					</a>
	    				@endif
	    			</div>
	    			@if(!empty($user) && $item->id==$user->id)
		    			<div class="dentist-payments" role="editor" style="display: none;">
							{{ Form::open(array('class' => 'edit-description', 'method' => 'post', 'url' => getLangUrl('profile/info') )) }}
								{!! csrf_field() !!}
								@foreach(config('trp.accepted_payment') as $ap)
									<label class="checkbox-label {!! in_array($ap, $user->accepted_payment) ? 'active' : '' !!}" for="checkbox-{{ $ap }}" >
										<input type="checkbox" class="special-checkbox" id="checkbox-{{ $ap }}" name="accepted_payment[]" value="{{ $ap }}" {!! in_array($ap, $user->accepted_payment) ? 'checked="checked"' : '' !!}>
										<i class="far fa-square"></i>
										{!! trans('trp.accepted-payments.'.$ap) !!}
									</label>
                                @endforeach
                                <br/>
                                <input type="hidden" name="field" value="accepted_payment" />
                                <input type="hidden" name="json" value="1" />
								<button type="submit" class="button">
									{!! nl2br(trans('trp.page.user.save')) !!}
								</button>
								<div class="alert alert-warning" style="display: none;">
								</div>
							{!! Form::close() !!}
		    			</div>
	    			@endif
				@endif
    			@if($item->description || (!empty($user) && $item->id==$user->id) )
	    			<div class="about-content" role="presenter">
	    				<span class="value-here" empty-value="{{ nl2br(trans('trp.page.user.description-empty')) }}">
		    				{!! $item->description ? nl2br($item->description) : nl2br(trans('trp.page.user.description-empty')) !!}
		    			</span>
	    				@if(!empty($user) && $item->id==$user->id)
	    					<a>
	    						<img src="{{ url('img-trp/pencil.png') }}">
	    					</a>
	    				@endif
	    			</div>
	    			@if(!empty($user) && $item->id==$user->id)
		    			<div class="about-content" role="editor" style="display: none;">
							{{ Form::open(array('class' => 'edit-description', 'method' => 'post', 'url' => getLangUrl('profile/info') )) }}
								{!! csrf_field() !!}
								<textarea class="input" name="description" placeholder="{!! nl2br(trans('trp.page.user.description-placeholder')) !!}">{{ $item->description }}</textarea>
                                <input type="hidden" name="field" value="description" />
                                <input type="hidden" name="json" value="1" />
								<button type="submit" class="button">{!! nl2br(trans('trp.page.user.save')) !!}</button>
								<div class="alert alert-warning" style="display: none;">
								</div>
							{!! Form::close() !!}
		    			</div>
	    			@endif
    			@endif
    			@if($item->photos->isNotEmpty() || (!empty($user) && $item->id==$user->id) )
	       			<div class="gallery-slider {!! count($item->photos) > 2 ? 'with-arrows' : '' !!}">
	    				<div class="gallery-flickity">
			    			@if( (!empty($user) && $item->id==$user->id) )
								<div class="slider-wrapper">
									{{ Form::open(array('class' => 'gallery-add', 'method' => 'post', 'files' => true)) }}
										<label for="add-gallery-photo" class="add-gallery-image slider-image cover image-label">
											<div class="plus-gallery-image">
												<i class="fas fa-plus"></i>
												<span>{!! nl2br(trans('trp.page.user.reviews-image')) !!}</span>
											</div>
								    		<div class="loader">
								    			<i class="fas fa-circle-notch fa-spin"></i>
								    		</div>
											<input type="file" name="image" id="add-gallery-photo" upload-url="{{ getLangUrl('profile/gallery') }}" sure-trans="{!! trans('trp.page.user.gallery-sure') !!}">
										</label>
									{!! Form::close() !!}
								</div>			    				
			    			@endif
				            @foreach($item->photos as $photo)
								<a href="{{ $photo->getImageUrl() }}" data-lightbox="user-gallery" class="slider-wrapper" photo-id="{{ $photo->id }}">
									<div class="slider-image cover" style="background-image: url('{{ $photo->getImageUrl(true) }}')">
										@if( (!empty($user) && $item->id==$user->id) )
											<div class="delete-gallery delete-button" sure="{!! trans('trp.page.user.gallery-sure') !!}">
												<i class="fas fa-times"></i>
											</div>
										@endif
									</div>
								</a>
							@endforeach
						</div>
	    			</div>
	    		@endif
    		</div>


		    @if($item->is_clinic && ( (!empty($user) && $item->id==$user->id) || $item->teamApproved->isNotEmpty() ) )
	    		<h2 class="black-left-line">
	    			{!! nl2br(trans('trp.page.user.team')) !!}
	    		</h2>

	    		<div class="team-container {!! count($item->teamApproved) > 3 ? 'with-arrows' : '' !!}">
		    		<div class="flickity">
		    			@if( (!empty($user) && $item->id==$user->id) )
							<div class="slider-wrapper">
								<a href="javascript:;" class="slider-image add-team-member"  data-popup-logged="add-team-popup">
									<div class="plus-team">
										<img src="{{ url('img-trp/add-member.png') }}">
										<span>
											{!! nl2br(trans('trp.page.user.team-add')) !!}
										</span>
									</div>
								</a>
							</div>
						@endif
			        	@foreach( !empty($user) && $item->id==$user->id ? $item->team : $item->teamApproved as $team)
							<a class="slider-wrapper{!! $team->approved ? '' : ' pending' !!}" href="{{ $team->clinicTeam->getLink() }}" dentist-id="{{ $team->clinicTeam->id }}">
								<div class="slider-image" style="background-image: url('{{ $team->clinicTeam->getImageUrl(true) }}')">
									@if( $team->clinicTeam->is_partner )
										<img class="tooltip-text" src="img-trp/mini-logo.png" text="{!! nl2br(trans('trp.common.partner')) !!} Clinic }}"/>
									@endif
									@if( (!empty($user) && $item->id==$user->id) )
										<div class="deleter" sure="{!! trans('trp.page.user.delete-sure', ['name' => $team->clinicTeam->getName() ]) !!}">
											<i class="fas fa-times"></i>
										</div>
									@endif
								</div>
							    <div class="slider-container">
							    	<h4>{{ $team->clinicTeam->getName() }}</h4>
								    <div class="ratings">
										<div class="stars">
											<div class="bar" style="width: {{ $team->clinicTeam->avg_rating/5*100 }}%;">
											</div>
										</div>
										<span class="rating">
											({{ intval($team->clinicTeam->ratings) }} reviews)
										</span>
									</div>
							    	@if( !$team->approved )
							    		<div class="approve-buttons clearfix">
								    		<div class="yes" action="{{ getLangUrl('profile/dentists/accept/'.$team->clinicTeam->id) }}">
								    			{!! nl2br(trans('trp.page.user.accept-dentist')) !!}
								    		</div>
								    		<div class="no" action="{{ getLangUrl('profile/dentists/reject/'.$team->clinicTeam->id) }}" sure="{!! trans('trp.page.user.delete-sure', ['name' => $team->clinicTeam->getName() ]) !!}">
								    			{!! nl2br(trans('trp.page.user.reject-dentist')) !!}
								    		</div>
								    	</div>
							    	@endif
							    </div>
						    	<div class="flickity-buttons clearfix">
						    		<div>
						    			{!! nl2br(trans('trp.common.see-profile')) !!}
						    		</div>
						    		<div href="{{ $team->clinicTeam->getLink() }}?popup-loged=submit-review-popup">
						    			{!! nl2br(trans('trp.common.submit-review')) !!}
						    		</div>
						    	</div>
							</a>
						@endforeach

						@if($item->invites->isNotEmpty())
				        	@foreach( $item->invites as $invite)
				        		@if(!$invite->invited_id && $invite->for_team)
									<a class="slider-wrapper" href="javascript:;" invite-id="{{ $invite->id }}">
										<div class="slider-image" style="background-image: url('{{ $invite->getImageUrl(true) }}')">
											@if( (!empty($user) && $item->id==$user->id) )
												<div class="delete-invite delete-button" sure="{!! trans('trp.page.user.delete-sure', ['name' => $invite->invited_name ]) !!}">
													<i class="fas fa-times"></i>
												</div>
											@endif
										</div>
									    <div class="slider-container">
									    	<div class="not-verified">Not verified</div>
									    	<h4>{{ $invite->invited_name }}</h4>
										    <div class="ratings">
												<div class="stars">
													<div class="bar" style="width: 0%;">
													</div>
												</div>
												<span class="rating">
													(0 reviews)
												</span>
											</div>
									    </div>
								    	<div class="flickity-buttons clearfix">
								    	</div>
									</a>
								@endif
							@endforeach
						@endif
					</div>
				</div>

		    @endif

		    @if( ($item->lat && $item->lon) || ( !empty($user) && $user->id==$item->id) )
				<h2 class="black-left-line">
					{!! nl2br(trans('trp.page.user.how-to-find')) !!}
					
				</h2>

				@if( ($item->lat && $item->lon) )
					<div class="map-container" id="profile-map" lat="{{ $item->lat }}" lon="{{ $item->lon }}">
					</div>
				@else
					<div class="alert alert-info">
						{!! nl2br(trans('trp.page.user.map-missing', [
							'link' => '<a href="javascript:;" class="open-edit alert-edit">',
							'endlink' => '</a>',
						])) !!}
						
					</div>
				@endif
			@endif
    	</div>
    </div>
</div>

@if(!empty($user))

	@if( $user->id==$item->id )
		@include('trp.popups.widget')
		@include('trp.popups.invite')
		@include('trp.popups.working-time')
		@if( $user->is_clinic )
			@include('trp.popups.add-member')
		@else
			@include('trp.popups.workplace')
		@endif
	@else
		@include('trp.popups.submit-review')
		@if(!$isTrusted && !$has_asked_dentist)
			@include('trp.popups.ask-dentist')
		@endif
	@endif
@endif
@include('trp.popups.detailed-review')



<script type="application/ld+json">
	{!! json_encode($schema, JSON_UNESCAPED_SLASHES) !!}
</script>

@endsection