@extends('admin')


@section('content')

	<h1 class="page-header">
	    Edit {{ $item->platform == 'vox' ? 'DentaVox' : 'Trusted Reviews' }} Seo Page "{{ $item->name }}"
	</h1>
	<!-- end page-header -->

	<div class="panel panel-inverse">
		<div class="row">
		    <!-- begin col-6 -->
		    <div class="col-md-12">
		        {{ Form::open(array('id' => 'pageseo-edit', 'class' => 'form-horizontal', 'method' => 'post', 'class' => 'form-horizontal', 'files' => true)) }}
		            {!! csrf_field() !!}
		                        
	            	<div class="panel panel-inverse panel-with-tabs custom-tabs">
		                <div class="panel-heading p-0">
		                    <!-- begin nav-tabs -->
		                    <div class="tab-overflow overflow-right">
		                        <ul class="nav nav-tabs nav-tabs-inverse">
		                            @foreach($langs as $code => $lang_info)
		                                <li class="{{ $loop->first ? 'active' : '' }}">
		                                    <a href="javascript:;" lang="{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a>
		                                </li>
		                            @endforeach
		                        </ul>
		                    </div>
		                </div>
		                <div class="tab-content">
		                	<div class="col-md-8">
			                    <div class="form-group clearfix">
			                        <label class="col-md-2 control-label">Seo Title</label>
			                        <div class="col-md-7" style="display: flex;"> 
			                            @foreach($langs as $code => $lang_info)
			                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
			                                    {{ Form::text('seo-title-'.$code, !empty($item) ? $item->{'seo_title:'.$code} : '', array('maxlength' => 128, 'class' => 'form-control input-title')) }}
			                                </div>
			                            @endforeach
			                        </div>
			                    </div>
			                    <div class="form-group clearfix">
			                        <label class="col-md-2 control-label">Seo Description</label>
			                        <div class="col-md-7" style="display: flex;"> 
			                            @foreach($langs as $code => $lang_info)
			                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
			                                    {{ Form::textarea('seo-description-'.$code, !empty($item) ? $item->{'seo_description:'.$code} : '', array('maxlength' => 516, 'class' => 'form-control input-title', 'style' => 'max-height: 68px;')) }}
			                                </div>
			                            @endforeach
			                        </div>
			                    </div>
			                    <div class="form-group clearfix">
			                        <label class="col-md-2 control-label">Social Title</label>
			                        <div class="col-md-7" style="display: flex;"> 
			                            @foreach($langs as $code => $lang_info)
			                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
			                                    {{ Form::text('social-title-'.$code, !empty($item) ? $item->{'social_title:'.$code} : '', array('maxlength' => 128, 'class' => 'form-control input-title')) }}
			                                </div>
			                            @endforeach
			                        </div>
			                    </div>
			                    <div class="form-group clearfix">
			                        <label class="col-md-2 control-label">Social Description</label>
			                        <div class="col-md-7" style="display: flex;"> 
			                            @foreach($langs as $code => $lang_info)
			                                <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }} lang-{{ $code  }} " style="flex: 1;">
			                                    {{ Form::textarea('social-description-'.$code, !empty($item) ? $item->{'social_description:'.$code} : '', array('maxlength' => 516, 'class' => 'form-control input-title', 'style' => 'max-height: 68px;')) }}
			                                </div>
			                            @endforeach
			                        </div>
			                    </div>

			                    <div class="form-group" style="margin-top: 60px;">
			                        <div class="col-md-7 col-md-offset-2">
			                            <button type="submit" class="btn btn-block btn-success">Save</button>
			                        </div>
			                    </div>
			                </div>
			                <div class="col-md-4">
			                	@if($item->id == 12 || $item->id == 15 || $item->id == 16 || $item->id == 17 || $item->id == 18 || $item->id == 19 || $item->id == 32 || $item->id == 33 )
			                		* Social image is shown dynamically
			                	@else
				                    <div class="form-group">
				                        <label class="col-md-3 control-label">Social Image</label>
				                        <div class="col-md-9">
				                            {{ Form::file('image', ['id' => 'image-input', 'accept' => 'image/jpg, image/jpeg, image/png']) }}<br/>
				                            * PNG, JPG до 2MB<br/>
				                            @if(!empty($item) && $item->hasimage)
				                                <a target="_blank" href="{{ $item->getImageUrl() }}">
				                                    <img src="{{ $item->getImageUrl(true) }}" style="max-width: 100%;" />
				                                </a>
				                                <a class="btn btn-sm btn-success" href="{{ url('cms/pages/edit/'.$item->id.'/removepic') }}" >
				                                    Remove Image
				                                </a>
				                            @endif
				                        </div>
				                    </div>
				                    <p>* If social image is empty, by default it shows {{ $item->platform == 'vox' ? 'https://dentavox.dentacoin.com/new-vox-img/logo-text.png' : 'https://reviews.dentacoin.com/img-trp/socials-cover.jpg' }}</p>
				                @endif
			                </div>

		                </div>
		            </div>

		        {{ Form::close() }}
		    </div>
		</div>
	</div>

@endsection