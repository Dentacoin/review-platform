@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
<!-- end page-header -->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('admin.page.'.$current_page.'.field.info') }}</th>
                                <th>{{ trans('admin.page.'.$current_page.'.field.video') }}</th>
                                <th>{{ trans('admin.page.'.$current_page.'.field.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $review)
                                <tr>
                                    <td>
                                        {{ $review->created_at->toTimeString() }} {{ $review->created_at->toFormattedDateString() }}<br/>
                                        {{ $review->user->getName() }}
                                        ->
                                        @if($review->clinic_id)
                                            <a href="{{ $review->clinic->getLink() }}" target="_blank">
                                                {{ $review->clinic->getName() }}
                                            </a>
                                        @endif
                                        @if($review->dentist_id)
                                            <a href="{{ $review->dentist->getLink() }}" target="_blank">
                                                {{ $review->dentist->getName() }}
                                            </a>
                                        @endif
                                        <br/>
                                        {{ trans('admin.page.'.$current_page.'.rating') }}: {{ $review->rating }}
                                    </td>
                                    <td>
                                        <iframe width="480" height="270" src="https://www.youtube.com/embed/{{ $review->youtube_id }}" frameborder="0" allow="encrypted-media" allowfullscreen></iframe>
                                    </td>
                                    <td>
                                        <a class="btn btn-primary btn-block" href="{{ url('cms/youtube/approve/'.$review->id) }}">
                                            <i class="fa fa-check"></i>
                                            {{ trans('admin.page.'.$current_page.'.approve') }}
                                        </a>
                                        <br/>
                                        <a class="btn btn-success btn-block" href="{{ url('cms/youtube/delete/'.$review->id) }}">
                                            <i class="fa fa-remove"></i>
                                            {{ trans('admin.page.'.$current_page.'.delete') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection