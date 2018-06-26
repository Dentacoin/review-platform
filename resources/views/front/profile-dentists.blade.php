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

                @if($user->team->isNotEmpty())

    	    		@foreach($user->team as $team)
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="col-lg-9 col-md-8 col-sm-9 work-wrapper">
                                    <a href="{{ getLangUrl('dentist/'.$team->clinicTeam->slug) }}">
                                        <img src="{{ $team->clinicTeam->getImageUrl(true) }}"/>
                                        <h4> {{ $team->clinicTeam->getName() }} </h4>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-3 work-buttons">
                                    @if($team->approved)
                                        <a href="{{ getLangUrl('profile/dentists/delete/'.$team->clinicTeam->id) }}" class="btn btn-primary rejected-button">
                                            {{ trans('front.page.profile.'.$current_subpage.'.dentist-delete') }}
                                        </a>
                                    @else
                                        <a href="{{ getLangUrl('profile/dentists/accept/'.$team->clinicTeam->id) }}" class="btn btn-success">
                                            {{ trans('front.page.profile.'.$current_subpage.'.dentist-accept') }}
                                        </a>
                                        <a href="{{ getLangUrl('profile/dentists/reject/'.$team->clinicTeam->id) }}" class="btn btn-danger rejected-button">
                                            {{ trans('front.page.profile.'.$current_subpage.'.dentist-reject') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
    	    		@endforeach
                @else
                    <div class="alert alert-info">
                        {{ trans('front.page.profile.'.$current_subpage.'.no-dentists') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ trans('front.page.profile.'.$current_subpage.'.invite-dentists.title') }}
                </h2>
            </div>
            <div class="panel-body">
                <p>
                    {{ trans('front.page.profile.'.$current_subpage.'.invite-dentists.hint') }}
                </p>

                <form method="post" action="{{ getLangUrl('profile/dentists/invite') }}">

                    <div class="dentist-suggester col-md-9 col-xs-12">

                        <input class="form-control" autocomplete="off" type="text" id="invitedentist" name="invitedentist" placeholder="{{ trans('front.page.profile.'.$current_subpage.'.dentist-name') }}" />
                        <input type="hidden" name="invitedentistid" id="invitedentistid" />

                        <div class="dentist-suggests">
                            <div class="loader">
                                <i class="fa fa fa-circle-o-notch fa-spin fa-2x fa-fw">
                                </i>
                            </div>
                            <div class="results">
                            </div>
                        </div>
                    </div>

                    <input class="btn btn-primary col-md-3 col-xs-12" type="submit" value="{{ trans('front.page.profile.'.$current_subpage.'.invite') }}"/>

                </form>
            </div>
        </div>
	</div>
</div>

@endsection