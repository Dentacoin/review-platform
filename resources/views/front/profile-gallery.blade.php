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
                <p>
                    {{ trans('front.page.profile.'.$current_subpage.'.hint') }}
                </p>

				@for($i=0;$i<8;$i++)
					<div class="col-md-3">
						{{ Form::open(array('id' => 'gallery-add-'.$i, 'method' => 'post', 'files' => true)) }}
						<div class="thumbnail gallery-pic {{ !empty($user->photos[$i]) ? '' : 'empty' }}" id="gallery-photo-{{ $i }}" data-position="{{ $i }}">
							@if(!empty($user->photos[$i]))
								<img src="{{ $user->photos[$i]->getImageUrl(true) }} ">
							@else
								<img src="">
							@endif
							<div class="loader">
								<i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
								{{ trans('front.page.profile.gallery.photo-uploading') }}
							</div>
							<label class="uploader" for="{{ 'gallery-image-'.$i }}">
								<i class="fa fa-plus"></i>
                				{{ trans('front.page.profile.'.$current_subpage.'.add-photo') }}
								{{ Form::file('image-'.$i, ['id' => 'gallery-image-'.$i]) }}
							</label>
							<a class="deleter" href="{{ getLangUrl('profile/gallery/delete/'.$i) }}" title="{{ trans('front.page.profile.'.$current_subpage.'.delete-photo') }}">
								<i class="fa fa-remove"></i>
							</a>
							<a class="editor" href="javascript:;" title="{{ trans('front.page.profile.'.$current_subpage.'.replace-photo') }}">
								<i class="fa fa-pencil"></i>
							</a>
							<div class="mobile-btns">
								<div class="row">
									<div class="col-xs-6">
										<a class="btn btn-block btn-primary editor-mobile" href="javascript:;">
											<i class="fa fa-pencil"></i> 
											{{ trans('front.page.profile.'.$current_subpage.'.replace-photo') }}
										</a>
									</div>
									<div class="col-xs-6">
										<a class="btn btn-block btn-default deleter-mobile" href="{{ getLangUrl('profile/gallery/delete/'.$i) }}">
											<i class="fa fa-remove"></i> 
											{{ trans('front.page.profile.'.$current_subpage.'.delete-photo') }}
										</a>
									</div>
								</div>
							</div>
						</div>
						{{ Form::close() }}
					</div>
				@endfor
			</div>
		</div>
	</div>
</div>

@endsection