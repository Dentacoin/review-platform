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

				<div class="form-group">
					<div class="col-md-12">
					  	<div class="btn-group btn-group-justified">
							<label for="option-iframe" class="btn btn-default">
								<b>{{ trans('front.page.profile.'.$current_subpage.'.iframe') }}</b><br/>
								{{ trans('front.page.profile.'.$current_subpage.'.iframe-hint') }}
							</label>
							<label for="option-js" class="btn btn-default">
								<b>{{ trans('front.page.profile.'.$current_subpage.'.js') }}</b><br/>
								{{ trans('front.page.profile.'.$current_subpage.'.js-hint') }}
						  	</label>
						</div>
					</div>
				</div>

				<div style="display: none;" id="option-mode">
					<div class="form-group">
					  	<div class="col-md-12">
					  		<h3>{{ trans('front.page.profile.'.$current_subpage.'.title-mode') }}</h3>
							<p>
								{{ trans('front.page.profile.'.$current_subpage.'.instruction-mode') }}							
							</p>
					  	</div>
					  	<div class="col-md-12 widget-modes">
					  		<label for="mode-all" class="btn-block">
					  			<input type="radio" id="mode-all" name="mode" value="0" checked="checked">
					  			{{ trans('front.page.profile.'.$current_subpage.'.mode-all') }}
					  		</label>
					  		<label for="mode-trusted" class="btn-block">
					  			<input type="radio" id="mode-trusted" name="mode" value="1">
					  			{{ trans('front.page.profile.'.$current_subpage.'.mode-trusted') }}
					  		</label>
						</div>
					</div>

				</div>

				<div style="display: none;" id="option-iframe" class="option-div">

					<div class="form-group">
					  	<div class="col-md-12">
					  		<h3>{{ trans('front.page.'.$current_page.'.title-embedd') }}</h3>
							<p>
								{!! nl2br(trans('front.page.'.$current_page.'.instruction-iframe')) !!}
							</p>
							<p>
								<img src="{{ url('img/iframe-instructions.png') }}" style="width: 100%;" />
								<br/>
								<br/>
							</p>
					  	</div>
					  	<div class="col-md-12">
					  		<textarea style="height: 200px;" class="form-control select-me"></textarea>
						</div>
					</div>

				</div>
				<div style="display: none;" id="option-js" class="option-div">

					<div class="form-group">
					  	<div class="col-md-12">
					  		<h3>{{ trans('front.page.'.$current_page.'.title-embedd') }}</h3>
							<p>
								{!! nl2br(trans('front.page.'.$current_page.'.instruction-js')) !!}
								<br/>
								<br/>
							</p>
					  	</div>
					  	<div class="col-md-12">
					  		<textarea style="height: 200px;" class="form-control select-me"></textarea>
						</div>
					</div>

				</div>

				<div style="display: none;" id="widget-preview">
					<div class="form-group">
					  	<div class="col-md-12">
					  		<h3>{{ trans('front.page.profile.'.$current_subpage.'.title-preview') }}</h3>
							<p>
						  		<iframe style="width: 100%; height: 50vh; border: none; outline: none;" src="{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/1') }}"></iframe>
						  	</p>
					  	</div>
					</div>
				</div>

			</div>
		</div>

	</div>
</div>

<script type="text/javascript">
	var widet_url = '{{ getLangUrl('widget/'.$user->id.'/'.$user->get_widget_token().'/{mode}') }}'
</script>

@endsection