@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title">
                    {{ trans('front.page.profile.'.$current_subpage.'.title') }}
                </h1>
            </div>
            <div class="panel-body">
            	@include('front.errors')
                <p>
	    			{{ trans('front.page.profile.'.$current_subpage.'.hint') }}
	    		</p>

                @if($user->my_workplace)

                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="col-lg-9 col-md-8 col-sm-9 work-wrapper">
                                <a href="{{ getLangUrl('dentist/'.$user->my_workplace->clinic->slug) }}">
                                    <img src="{{ $user->my_workplace->clinic->getImageUrl(true) }}"/>
                                    <h4> {{ $user->my_workplace->clinic->getName() }} </h4>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-3 work-buttons">
                               @if($user->my_workplace->approved )
                                    <a href="{{ getLangUrl('profile/clinics/delete/'.$user->my_workplace->clinic->id) }}" class="btn btn-primary rejected-button">
                                        {{ trans('front.page.profile.'.$current_subpage.'.clinic-leave') }}
                                    </a>
                                @else
	                                <p>
	                                	{{ trans('front.page.profile.'.$current_subpage.'.clinic-pending') }}
	                                </p>                                    
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        {{ trans('front.page.profile.'.$current_subpage.'.no-clinic') }}
                    </div>
                @endif
            </div>
        </div>

        @if(!$user->my_workplace)

	        <div class="panel panel-default">
	            <div class="panel-heading">
	                <h2 class="panel-title">
	                    {{ trans('front.page.profile.'.$current_subpage.'.invite-clinic.title') }}
	                </h2>
	            </div>
	            <div class="panel-body">
	                <p>
	                	{{ trans('front.page.profile.'.$current_subpage.'.invite-clinic.hint') }}
	                </p>

			        <form method="post" action="{{ getLangUrl('profile/clinics/invite') }}">

	                    <div class="clinic-suggester col-md-9 col-xs-12">

	                    	<input class="form-control" autocomplete="off" type="text" id="joinclinic" name="joinclinic" placeholder="{{ trans('front.page.profile.'.$current_subpage.'.clinic-name') }}" />
	                    	<input type="hidden" name="joinclinicid" id="joinclinicid" />

	                    	<div class="clinic-suggests">
								<div class="loader">
									<i class="fa fa fa-circle-o-notch fa-spin fa-2x fa-fw">
									</i>
								</div>
								<div class="results">
								</div>
							</div>
	                    </div>

						<input class="btn btn-primary col-md-3 col-xs-12" type="submit" value="{{ trans('front.page.profile.'.$current_subpage.'.join') }}"/>

	                </form>
	            </div>
	        </div>
	    @endif
	</div>
</div>

@endsection