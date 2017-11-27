@extends('vox')

@section('content')

	<div class="container">
			<div class="col-md-8 col-md-offset-2">
				<br/><br/>
				<div class="panel panel-default">
				<div class="panel-body">
					
					<h1>
		  				{{ trans('vox.page.'.$current_page.'.title') }}
		  			</h1>
		  			<br/>

	      			<form action="{{ getLangUrl('recover/'.$id.'/'.$hash) }}" method="post" class="form-horizontal">
	      				{!! csrf_field() !!}

	      				<p>
	      					{{ trans('vox.page.'.$current_page.'.hint') }}
	      				</p>
		  				<br/>
	      				
	        			<div class="form-group">
						  	<label class="control-label col-md-4">
						  		{{ trans('vox.page.'.$current_page.'.password') }}
						  	</label>
						  	<div class="col-md-8">
						    	<input type="password" name="password" class="form-control" required>
						    </div>
						</div>
					  	<div class="form-group">
						  	<label class="control-label col-md-4">
						  		{{ trans('vox.page.'.$current_page.'.password-repeat') }}
						  	</label>
						  	<div class="col-md-8">
						    	<input type="password" name="password-repeat" class="form-control" required>
						    </div>
						</div>
						<div class="form-group">
							<div class="col-md-12">
								<button class="btn btn-primary btn-block db" type="submit">
							  		{{ trans('vox.page.'.$current_page.'.submit') }}
						  		</button>
								
								@include('front.errors')
							</div>
						</div>
	        			
	      			</form>
	      		</div>
	      	</div>
		</div>

	</div>
	
@endsection