@extends('admin')

@section('content')

    <h1 class="page-header">
        {{ trans('admin.page.'.$current_page.'.title') }}
    </h1>

    <!-- end page-header -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand">
                            <i class="fa fa-expand"></i>
                        </a>
                    </div>
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
                </div>
                <div class="panel-body">

                    <div class="clearfix">
                        @if(in_array($type, ['trp', 'api']))
                            <div class="row">
                                @if($type == 'trp')
                                    <div class="col-md-8">
                                        <a href="{{ url('cms/logs/'.$type) }}?date={{ date('Y-m-d') }}" class="btn btn-{{ !request('date') || request('date') == date('Y-m-d') ? 'primary' : 'info' }}">
                                            Today
                                        </a>
                                        @foreach($logDates as $logDate)
                                            <a href="{{ url('cms/logs/'.$type) }}?date={{ date('Y-m-d', $logDate->timestamp) }}" class="btn btn-{{ request('date') == date('Y-m-d', $logDate->timestamp) ? 'primary' : 'info' }}">
                                                {{ date('d-m-Y', $logDate->timestamp) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="col-md-{{ $type == 'trp' ? '4' : '12' }}">
                                    <a href="{{ url('cms/logs/'.$type) }}?clear=1" class="btn btn-danger pull-right" onclick="return confirm('Are you sure?')">
                                        clear
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="row panel-body">
                        <h4>Errors</h4>
                    </div>

                    <pre>
                        @if($type == 'api_civic')
                            {!! @file_get_contents( base_path().'\/../api/storage/logs/civic.log' ) !!}
                        @elseif($type == 'api_withdraw')
                            {!! @file_get_contents( base_path().'\/../api/storage/logs/withdraw.log' ) !!}
                        @elseif($type == 'api-ban-appeals')
                            {!! @file_get_contents( base_path().'\/../api/storage/logs/ban-appeals.log' ) !!}
                        @elseif($type == 'too-fast-bans')
                            {!! @file_get_contents( base_path().'/storage/logs/'.$type.'.log' ) !!}
                        @else
                            {!! @file_get_contents( base_path().'\/../'.$type.'/storage/logs/laravel'.($type == 'trp' ? '-'.(request('date') ?? date('Y-m-d')) : '').'.log' ) !!}
                        @endif
                    </pre>
                </div>
            </div>
        </div>
    </div>

@endsection