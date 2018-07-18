@extends('admin')

@section('content')

<h1 class="page-header"> {{ trans('admin.page.'.$current_page.'.title-edit') }} </h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-edit') }} </h4>
            </div>
            <div class="panel-body">
                {!! Form::open(array('url' => url('cms/'.$current_page.'/edit/'.$item->id), 'method' => 'post', 'class' => 'form-horizontal')) !!}
                    {!! csrf_field() !!}

                        <div class="form-group">
                        @foreach( $fields as $key => $info)
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.form-'.$key) }}</label>
                            <div class="col-md-4">
                                @if( $key == 'type')
                                    {{ Form::select( $key , $info['values'] , ($item->is_dentist ? ( $item->is_clinic ? 'clinic' : 'dentist' ) : 'patient') , array(
                                        'class' => 'form-control',
                                        'style' => ''.(!empty($info['multiple']) ? 'height: 200px;' : '')
                                    )) }}
                                @elseif( $info['type'] == 'text')
                                    {{ Form::text( $key, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                    @if($key=='fb_id' && $item->$key)
                                        <a href="https://facebook.com/{{ $item->$key }}" target="_blank">Open FB profile</a>
                                    @endif
                                @elseif( $info['type'] == 'textarea')
                                    {{ Form::textarea( $key, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                @elseif( $info['type'] == 'bool')
                                    {{ Form::checkbox( $key, 1, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                @elseif( $info['type'] == 'datepicker')
                                    {{ Form::text( $key, !empty($item->$key) ? $item->$key->format('d.m.Y') : '' , array('class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
                                @elseif( $info['type'] == 'datetimepicker')
                                    {{ Form::text( $key, !empty($item->$key) ? $item->$key->format('Y.m.d H:i:s') : '' , array('class' => 'form-control datetimepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
                                @elseif( $info['type'] == 'country')  
                                    {{ Form::select( $key , \App\Models\Country::get()->pluck('name', 'id')->toArray() , $item->$key , array('class' => 'form-control country-select') ) }}
                                @elseif( $info['type'] == 'city')  
                                    {{ Form::select( $key , $item->country_id ? \App\Models\City::where('country_id', $item->country_id)->get()->pluck('name', 'id')->toArray() : [] , $item->$key , array('class' => 'form-control city-select') ) }}
                                @elseif( $info['type'] == 'avatar')
                                    @if($item->hasimage)
                                        <a class="thumbnail" href="{{ $item->getImageUrl() }}" target="_blank">
                                            <img src="{{ $item->getImageUrl(true) }}">
                                        </a>
                                        <a class="btn btn-primary" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/deleteavatar') }}" onclick="return confirm('{{ trans('admin.common.sure') }}')">
                                            <i class="fa fa-remove"></i> {{ trans('admin.page.'.$current_page.'.delete-avatar') }}
                                        </a>
                                    @else
                                        <div class="alert alert-info">
                                            {{ trans('admin.page.'.$current_page.'.no-avatar') }}
                                        </div>
                                    @endif
                                @elseif( $info['type'] == 'select')  
                                    {{ Form::select( $key , $info['values'] , $item->$key , array(
                                        'class' => 'form-control'.(!empty($info['multiple']) ? ' multiple' : '') , 
                                        (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' , 
                                        (!empty($info['multiple']) ? 'multiple' : 'nothing') => 'multiple',
                                        'style' => ''.(!empty($info['multiple']) ? 'height: 200px;' : '')
                                    )) }}
                                @endif
                            </div>
                            @if($loop->index%2)
                        </div>
                        <div class="form-group">
                            @endif
                        @endforeach
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.form-created-at') }}</label>
                            <div class="col-md-4">
                                {{ $item->created_at->toDateTimeString() }}
                            </div>
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-4">
                                
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.form-self-deleted') }}</label>
                            <div class="col-md-4">
                                @if($item->self_deleted)
                                    <span style="color: red; font-weight: bold; font-size: 16px;">
                                        Yes
                                    </span>
                                @else
                                    No
                                @endif
                            </div>
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-4">
                                
                            </div>
                        </div>

                    <div class="form-group">
                        <div class="col-md-4">
                            <a href="{{ url('cms/users/user-data/'.$item->id) }}" target="_blank" class="btn btn-sm btn-warning form-control">Export Personal Data</a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ url('cms/users/loginas/'.$item->id) }}" target="_blank" class="btn btn-sm btn-primary form-control"> {{ trans('admin.page.profile.loginas') }} </a>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control"> {{ trans('admin.common.save') }} </button>
                        </div>
                    </div>

                {!! Form::close() !!}
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>

@if($item->photos->isNotEmpty())
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-photos') }} </h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        @foreach($item->photos as $photo)
                            <div class="col-md-3">
                                <div class="thumbnail">
                                    <img src="{{ $photo->getImageUrl(true) }} ">
                                </div>
                                <a class="btn btn-primary" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/deletephoto/'.$loop->index) }}" onclick="return confirm('{{ trans('admin.common.sure') }}')">
                                    <i class="fa fa-remove"></i> {{ trans('admin.page.'.$current_page.'.delete-photo') }}
                                </a>
                            </div>
                            @if($loop->index==3 && !$loop->last)
                                </div>
                                <div class="row">
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif

@if($item->is_dentist)
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-reviews-in') }} </h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'users',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'user'              => array('template' => 'admin.parts.table-reviews-user'),
                            'dentist'           => array('template' => 'admin.parts.table-reviews-dentist'),
                            'rating'            => array(),
                            'upvotes'            => array(),
                            'verified'              => array('format' => 'bool'),
                            'link'              => array('template' => 'admin.parts.table-reviews-link'),
                            'delete'            => array('format' => 'delete'),
                        ],
                        'table_subpage' => 'reviews',
                        'table_data' => $item->reviews_in,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>

@else

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-reviews-out') }} </h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'users',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'user'              => array('template' => 'admin.parts.table-reviews-user'),
                            'dentist'           => array('template' => 'admin.parts.table-reviews-dentist'),
                            'rating'            => array(),
                            'upvotes'            => array(),
                            'verified'              => array('format' => 'bool'),
                            'link'              => array('template' => 'admin.parts.table-reviews-link'),
                            'delete'            => array('format' => 'delete'),
                        ],
                        'table_subpage' => 'reviews',
                        'table_data' => $item->reviews_out,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>

@endif


@if($item->vox_rewards->isNotEmpty())
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-vox-rewards') }} </h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'vox-rewards',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'vox_id'              => array('template' => 'admin.parts.table-vox-rewards-user'),
                            'reward'           => array(),
                            'delete'              => array('template' => 'admin.parts.table-vox-rewards-delete'),
                        ],
                        'table_data' => $item->vox_rewards,
                        'table_subpage' => 'vox-rewards',
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>
@endif


@if($unfinished->isNotEmpty())
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> Unfinished surveys </h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'vox-unfinished',
                        'table_fields' => [
                            'title'              => array(),
                            'delete'              => array('template' => 'admin.parts.table-vox-unfinished-delete'),
                        ],
                        'table_data' => $unfinished,
                        'table_subpage' => 'vox-unfinished',
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>
@endif

@if($item->history->isNotEmpty())
    

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title-transactions') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="panel-body">
                        @include('admin.parts.table', [
                            'table_id' => 'transactions',
                            'table_fields' => [
                                'created_at'        => array('format' => 'datetime'),
                                'amount'              => array(),
                                'address'              => array(),
                                'tx_hash'              => array('template' => 'admin.parts.table-transactions-hash'),
                                'status'              => array(),
                                'type'              => array(),
                            ],
                            'table_data' => $item->history,
                            'table_pagination' => false,
                            'pagination_link' => array()
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($item->bans->isNotEmpty())
    

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title-bans') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="panel-body">
                        @include('admin.parts.table', [
                            'table_id' => 'bans',
                            'table_fields' => [
                                'created_at'        => array('format' => 'datetime'),
                                'domain'              => array(),
                                'expires'              => array('template' => 'admin.parts.table-bans-expires'),
                                'type'              => array(),
                                'delete'              => array('template' => 'admin.parts.table-bans-delete'),
                            ],
                            'table_data' => $item->bans,
                            'table_pagination' => false,
                            'pagination_link' => array()
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif


@if($item->vox_cashouts->isNotEmpty())
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">OLD! {{ trans('admin.page.'.$current_page.'.title-vox-cashouts') }} OLD!</h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'vox-cashouts',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'reward'           => array(),
                            'address'           => array(),
                            'tx_hash'           => array(),
                        ],
                        'table_data' => $item->vox_cashouts,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>
@endif


@endsection