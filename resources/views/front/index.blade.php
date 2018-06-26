@extends('front')

@section('content')

<div class="jumbotron">
	<div class="container ribbon-container">
		<div class="ribbon">
			<b>{{ $users_count }}</b>
			{{ trans('front.page.index.users-count') }}
			<b class="second">{{ $dentist_count }}</b>
			{{ trans('front.page.index.dentist-count') }}
			<div class="left-t"></div>
			<div class="right-t"></div>
		</div>
	</div>
	<div class="search-bar">
		<div class="container">
			<h1>
				{{ trans('front.page.'.$current_page.'.title') }}
			</h1>
			{!! Form::open(array('url' => getLangUrl('dentists'), 'method' => 'get', 'class' => 'form-horizontal')) !!}
				<div class="col-md-3">
					<div class="location">
						{{ Form::text( 'username' , null , array('class' => 'form-control user-input', 'autocomplete' => 'off', 'placeholder' =>  trans('front.page.'.$current_page.'.name')) ) }}
						<div class="location-suggester">
							<div class="loader">
								<i class="fa fa fa-circle-o-notch fa-spin fa-2x fa-fw">
								</i>
							</div>
							<div class="results">
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					{{ Form::select( 'category' , ['' => 'Any dentist'] + $categories , null , array('class' => 'form-control') ) }}
				</div>
				<div class="col-md-1" style="text-align: center; line-height: 36px; color: white; font-size: 14px;">
					{{ trans('front.page.'.$current_page.'.from') }}
				</div>
				<div class="col-md-3">
					<div class="location">
						{{ Form::text( 'location' , $placeholder , array('class' => 'form-control location-input', 'autocomplete' => 'off') ) }}
						{{ Form::hidden( 'country' , $country_id, ['class' => 'country_id']  ) }}
						{{ Form::hidden( 'city' , $city_id, ['class' => 'city_id']  ) }}
						<div class="location-suggester">
							<div class="loader">
								<i class="fa fa fa-circle-o-notch fa-spin fa-2x fa-fw">
								</i>
							</div>
							<div class="results">
							</div>
						</div>
					</div>
					<label for="all-locations" class="all-locations">
						<input type="checkbox" name="all_locations" id="all-locations" value="1" />
						Show dentists from all locations
					</label>
				</div>
				<div class="col-md-2">
                    {{ Form::submit( trans('front.page.index.submit'), ['class' => 'btn btn-primary btn-block'] ) }}
				</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>

<div class="container reasons">
	<h2>
		{{ trans('front.page.'.$current_page.'.why-join') }}
	</h2>
	<div class="col-md-6">
		<div class="colorful">
			<h3>
				{{ trans('front.page.'.$current_page.'.why-patient') }}
			</h3>

			<div class="media">
				<div class="media-left">
					<i class="fa fa-bullhorn"></i>
				</div>
				<div class="media-body">
					<h4 class="media-heading">
						{{ trans('front.page.'.$current_page.'.why-patient-1-title') }}
					</h4>
					{{ trans('front.page.'.$current_page.'.why-patient-1-content') }}
				</div>
			</div>
			<div class="media">
				<div class="media-left">
					<i class="fa fa-balance-scale"></i>
				</div>
				<div class="media-body">
					<h4 class="media-heading">
						{{ trans('front.page.'.$current_page.'.why-patient-2-title') }}
					</h4>
					{{ trans('front.page.'.$current_page.'.why-patient-2-content') }}
				</div>
			</div>
			<div class="media">
				<div class="media-left">
					<i class="fa fa-certificate"></i>
				</div>
				<div class="media-body">
					<h4 class="media-heading">
						{{ trans('front.page.'.$current_page.'.why-patient-3-title') }}
					</h4>
					{{ trans('front.page.'.$current_page.'.why-patient-3-content') }}
				</div>
			</div>
			<div class="media">
				<div class="media-left">
					<i class="fa fa-trophy"></i>
				</div>
				<div class="media-body">
					<h4 class="media-heading">
						{{ trans('front.page.'.$current_page.'.why-patient-4-title') }}
					</h4>
					{{ trans('front.page.'.$current_page.'.why-patient-4-content') }}
				</div>
			</div>
		</div>

	</div>
	<div class="col-md-6">
		<div class="colorful">
			<h3>
				{{ trans('front.page.'.$current_page.'.why-dentist') }}
			</h3>

			<div class="media">
				<div class="media-left">
					<i class="fa fa-comments-o"></i>
				</div>
				<div class="media-body">
					<h4 class="media-heading">
						{{ trans('front.page.'.$current_page.'.why-dentist-1-title') }}
					</h4>
					{{ trans('front.page.'.$current_page.'.why-dentist-1-content') }}
				</div>
			</div>
			<div class="media">
				<div class="media-left">
					<i class="fa fa-bar-chart"></i>
				</div>
				<div class="media-body">
					<h4 class="media-heading">
						{{ trans('front.page.'.$current_page.'.why-dentist-2-title') }}
					</h4>
					{{ trans('front.page.'.$current_page.'.why-dentist-2-content') }}
				</div>
			</div>
			<div class="media">
				<div class="media-left">
					<i class="fa fa-line-chart"></i>
				</div>
				<div class="media-body">
					<h4 class="media-heading">
						{{ trans('front.page.'.$current_page.'.why-dentist-3-title') }}
					</h4>
					{{ trans('front.page.'.$current_page.'.why-dentist-3-content') }}
				</div>
			</div>
			<div class="media">
				<div class="media-left">
					<i class="fa fa-trophy"></i>
				</div>
				<div class="media-body">
					<h4 class="media-heading">
						{{ trans('front.page.'.$current_page.'.why-dentist-4-title') }}
					</h4>
					{{ trans('front.page.'.$current_page.'.why-dentist-4-content') }}
				</div>
			</div>
		</div>
	</div>

</div>

@endsection