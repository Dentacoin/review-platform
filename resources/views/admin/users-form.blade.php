@extends('admin')

@section('content')

<h1 class="page-header"> 
    {{ trans('admin.page.'.$current_page.'.title-edit') }} 
    @if( $item->getSameIPUsers() )
        <a class="label label-danger" href="{{ url('cms/users/edit/'.$item->id) }}#logins-list">Click for Suspicious Logins</a>
    @endif
</h1>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-3 control-label">User Type</label>
                                <div class="col-md-{{ $item->is_dentist ? '3' : '4' }}">
                                    @include('admin.parts.user-field',[
                                        'key' => 'type',
                                        'info' => $fields['type']
                                    ])
                                </div>
                                @if($item->is_dentist)
                                    <label class="col-md-1 control-label">Status</label>
                                    <div class="col-md-3">
                                        @include('admin.parts.user-field',[
                                            'key' => 'status',
                                            'info' => $fields['status']
                                        ])
                                    </div>
                                @endif
                                @if(!$item->is_dentist)
                                    <label class="col-md-3 control-label">User ID</label>
                                @endif
                                <div class="col-md-2">
                                    {{ Form::text( 'id', $item->id, array('class' => 'form-control', 'disabled' ) ) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Name</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'name',
                                        'info' => $fields['name']
                                    ])
                                </div>
                            </div>
                            @if($item->is_dentist && !$item->is_clinic)
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Title</label>
                                    <div class="col-md-9">
                                        @include('admin.parts.user-field',[
                                            'key' => 'title',
                                            'info' => $fields['title']
                                        ])
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <label class="col-md-3 control-label">Gender</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'gender',
                                        'info' => $fields['gender']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Birth Year</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'birthyear',
                                        'info' => $fields['birthyear']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Email</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'email',
                                        'info' => $fields['email']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Country</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'country_id',
                                        'info' => $fields['country_id']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">City</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'city_name',
                                        'info' => $fields['city_name']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">ZIP code</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'zip',
                                        'info' => $fields['zip']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Dental Practice</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'address',
                                        'info' => $fields['address']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Website / Facebook URL</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'website',
                                        'info' => $fields['website']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Facebook ID</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'fb_id',
                                        'info' => $fields['fb_id']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Civic ID</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'civic_id',
                                        'info' => $fields['civic_id']
                                    ])
                                </div>
                            </div>
                            @if($item->is_dentist)
                                <div class="form-group">
                                    <label class="col-md-3 control-label">New Password</label>
                                    <div class="col-md-9">
                                        @include('admin.parts.user-field',[
                                            'key' => 'password',
                                            'info' => $fields['password']
                                        ])
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <label class="col-md-3 control-label">DCN Address</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'dcn_address',
                                        'info' => $fields['dcn_address']
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-3 control-label">Profile photo</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'avatar',
                                        'info' => $fields['avatar']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12" style="text-align: right;">
                                    <span style="color: black; padding-right: 5px;">Self deleted:</span>
                                    @if($item->self_deleted)
                                        <span style="color: red; font-weight: bold; font-size: 16px;">
                                            Yes
                                        </span>
                                    @else
                                        <span style="color: black;">No</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Dentacoin partner?</label>
                                <div class="col-md-3">
                                    @include('admin.parts.user-field',[
                                        'key' => 'is_partner',
                                        'info' => $fields['is_partner']
                                    ])
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ url('cms/users/loginas/'.$item->id) }}" target="_blank" class="btn btn-sm btn-primary form-control"> {{ trans('admin.page.profile.loginas') }} </a>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Civic photo ID?</label>
                                <div class="col-md-3">
                                    @include('admin.parts.user-field',[
                                        'key' => 'civic_kyc',
                                        'info' => $fields['civic_kyc']
                                    ])
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ url('cms/users/user-data/'.$item->id) }}" target="_blank" class="btn btn-sm btn-warning form-control">Export Personal Data</a>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Allow withdraw</label>
                                <div class="col-md-3">
                                    @include('admin.parts.user-field',[
                                        'key' => 'allow_withdraw',
                                        'info' => $fields['allow_withdraw']
                                    ])
                                </div>
                                <div class="col-md-6">
                                    @if($item->deleted_at)
                                        <a href="{{ url('cms/users/restore/'.$item->id) }}" class="btn btn-sm btn-info form-control"> Restore </a>
                                    @else
                                        <a href="{{ url('cms/users/delete/'.$item->id) }}" class="btn btn-sm btn-danger form-control"> Delete </a>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control"> {{ trans('admin.common.save') }} </button>
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4 style="margin-bottom: 20px;">TRUSTED REVIEWS</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-3 control-label">Average rating</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'avg_rating',
                                        'info' => $fields['avg_rating']
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-3 control-label">Ratings</label>
                                <div class="col-md-9">
                                    @include('admin.parts.user-field',[
                                        'key' => 'ratings',
                                        'info' => $fields['ratings']
                                    ])
                                </div>
                            </div>
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
                            'rating'            => array(),
                            'upvotes'            => array(),
                            'verified'              => array('format' => 'bool'),
                            'delete'            => array('format' => 'delete'),
                        ],
                        'table_subpage' => 'reviews',
                        'table_data' => $item->reviews_in(),
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
            <h4 style="margin-bottom: 20px;">DENTAVOX</h4>
        </div>
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
                                'created_at'        => array('format' => 'datetime', 'label' => 'Received'),
                                'type'              => array(),
                                'duration'              => array('template' => 'admin.parts.table-bans-duration'),
                                //'domain'              => array(),
                                'expires'              => array('template' => 'admin.parts.table-bans-expires'),
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

@if($item->logins->isNotEmpty())
    <div class="row" id="logins-list">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">User Logins</h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'vox-cashouts',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'ip'                => array('template' => 'admin.parts.table-logins-user'),
                            'platform'          => array(),
                        ],
                        'table_data' => $item->logins,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>
@endif

@if($emails)
    <div class="row" id="logins-list">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Activity History</h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'user-emails',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'template_id'       => array('template' => 'admin.parts.table-email-template'),
                        ],
                        'table_data' => $emails,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>
@endif


@endsection