@extends('admin')

@section('content')

<h1 class="page-header"> 
    {{ trans('admin.page.'.$current_page.'.title-edit') }}
    @if( $item->getSameIPUsers() && !$item->is_dentist )
        <a class="label label-danger" href="{{ url('cms/users/edit/'.$item->id) }}#logins-list">Click for Suspicious Logins</a>
    @endif
</h1>
<!-- end page-header -->

@if($item->status == 'added_by_clinic_new')
    <div class="alert alert-danger">
        This dentist is added by unapproved clinic, please approve the clinic first.
    </div>
@endif

@if($item->status == 'new' && $item->is_dentist && !$item->is_clinic && $item->my_workplace->isNotEmpty())
    <div class="alert alert-info">
        @foreach($item->my_workplace as $wp)
            This dentist works in clinic <a href="{{ url('cms/users/edit/'.$wp->clinicWithTrashed->id) }}">{{ $wp->clinicWithTrashed->name }}</a> <br/>
        @endforeach
    </div>
@endif

@if($item->status == 'added_by_dentist_new' && $item->is_clinic && $item->team->isNotEmpty())
    <div class="alert alert-info">
        @foreach($item->team as $t)
            This clinic is listed as a workplace for new dentist <a href="{{ url('cms/users/edit/'.$t->clinicTeamWithTrashed->id) }}">{{ $t->clinicTeamWithTrashed->name }}</a> <br/>
        @endforeach
    </div>
@endif

@if($item->status == 'new' && $item->is_clinic && $item->team->isNotEmpty())
    <div class="alert alert-info">
        @foreach($item->team as $t)
            This clinic added a new dentist <a href="{{ url('cms/users/edit/'.$t->clinicTeamWithTrashed->id) }}">{{ $t->clinicTeamWithTrashed->name }}</a> <br/>
        @endforeach
    </div>
@endif

@if(!empty($item->old_unclaimed_profile))
    <div class="alert alert-info">
        This dentist is old added by patient with unclaimed profile.
    </div>
@endif

@if(!empty($item->invited_himself_reg))
    <div class="alert alert-info">
        This dentist added himself as practice on registraion
    </div>
@endif

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

                            @if($item->platform == 'external')
                                <span style="color: blue; font-weight: bold;">External Patient</span>
                            @endif
                            @if($item->id == 79003)
                                <div class="col-md-9 col-md-offset-2" style="color: red; margin-bottom: 20px;">This dentist has been rejected because he already exists - ID: 6119</div>
                            @endif
                            <div class="form-group">
                                <label class="col-md-2 control-label">User Type</label>
                                <div class="col-md-10">
                                    <div class="flex" style="align-items: baseline;justify-content: space-between;">
                                        <div>
                                            @include('admin.parts.user-field',[
                                                'key' => 'type',
                                                'info' => $fields['type']
                                            ])
                                        </div>
                                        @if($item->is_dentist)
                                            <div>
                                                <label class="control-label" style="padding-right: 10px;">Status</label>
                                                <div style="display: inline-block;">
                                                    <select class="form-control" name="status">
                                                        @if($item->status == 'added_new' || $item->status == 'added_rejected')
                                                            <option value="added_new" {{ $item->status == 'added_new' ? 'selected="selected"' : ''}} >Added New</option>
                                                            <option value="added_approved" {{ $item->status == 'added_approved' ? 'selected="selected"' : ''}} >Added Approved</option>
                                                            <option value="added_rejected" {{ $item->status == 'added_rejected' ? 'selected="selected"' : ''}} >Added Rejected</option>

                                                        @elseif($item->status == 'added_approved')
                                                            <option value="approved" {{ $item->status == 'approved' ? 'selected="selected"' : ''}} >Approved</option>
                                                            <option value="added_new" {{ $item->status == 'added_new' ? 'selected="selected"' : ''}} >Added New</option>
                                                            <option value="added_approved" {{ $item->status == 'added_approved' ? 'selected="selected"' : ''}} >Added Approved</option>
                                                            <option value="added_rejected" {{ $item->status == 'added_rejected' ? 'selected="selected"' : ''}} >Added Rejected</option>

                                                        @elseif($item->status == 'admin_imported')
                                                            <option value="admin_imported" {{ $item->status == 'admin_imported' ? 'selected="selected"' : ''}} >Imported by Admin</option>
                                                            <option value="approved" {{ $item->status == 'approved' ? 'selected="selected"' : ''}} >Approved</option>

                                                        @elseif($item->status == 'added_by_clinic_rejected' || $item->status == 'added_by_clinic_unclaimed')
                                                            <option value="added_by_clinic_unclaimed" {{ $item->status == 'added_by_clinic_unclaimed' ? 'selected="selected"' : ''}} >Added by Clinic Approved</option>
                                                            <option value="added_by_clinic_rejected" {{ $item->status == 'added_by_clinic_rejected' ? 'selected="selected"' : ''}} >Added by Clinic Rejected</option>

                                                        @elseif($item->status == 'added_by_clinic_new')
                                                            <option value="added_by_clinic_new" {{ $item->status == 'added_by_clinic_new' ? 'selected="selected"' : ''}} >Added by Clinic New</option>

                                                        @elseif($item->status == 'added_by_clinic_claimed')
                                                            <option value="added_by_clinic_claimed" {{ $item->status == 'added_by_clinic_claimed' ? 'selected="selected"' : ''}} >Added by Clinic Claimed</option>

                                                        @elseif($item->status == 'added_by_dentist_new' || $item->status == 'added_by_dentist_rejected' || $item->status == 'added_by_dentist_unclaimed')
                                                            <option value="added_by_dentist_new" {{ $item->status == 'added_by_dentist_new' ? 'selected="selected"' : ''}} >Added by Dentist New</option>
                                                            <option value="added_by_dentist_unclaimed" {{ $item->status == 'added_by_dentist_unclaimed' ? 'selected="selected"' : ''}} >Added by Dentist Approved</option>
                                                            <option value="added_by_dentist_rejected" {{ $item->status == 'added_by_dentist_rejected' ? 'selected="selected"' : ''}} >Added by Dentist Rejected</option>     

                                                        @elseif($item->status == 'added_by_dentist_claimed')
                                                            <option value="added_by_dentist_claimed" {{ $item->status == 'added_by_dentist_claimed' ? 'selected="selected"' : ''}} >Added by Dentist Claimed</option>

                                                        @elseif($item->status == 'duplicated_email')
                                                            <option value="duplicated_email" {{ $item->status == 'duplicated_email' ? 'selected="selected"' : ''}} >Duplicated Email</option>

                                                        @else
                                                            <option value="new" {{ $item->status == 'new' ? 'selected="selected"' : ''}} >New</option>
                                                            <option value="approved" {{ $item->status == 'approved' ? 'selected="selected"' : ''}} >Approved</option>
                                                            <option value="pending" {{ $item->status == 'pending' ? 'selected="selected"' : ''}} >Suspicious</option>
                                                            <option value="rejected" {{ $item->status == 'rejected' ? 'selected="selected"' : ''}} >Rejected</option>
                                                            <option value="dentist_no_email" {{ $item->status == 'dentist_no_email' ? 'selected="selected"' : ''}} >Dentist No Email</option>
                                                        @endif
                                                        <option value="test" {{ $item->status == 'test' ? 'selected="selected"' : ''}} >Test</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- @if($item->invited_by)
                                                <div>
                                                    <label class="control-label" style="padding-right: 10px;">Ownership</label>
                                                    <div style="display: inline-block;">
                                                        @include('admin.parts.user-field',[
                                                            'key' => 'ownership',
                                                            'info' => $fields['ownership']
                                                        ])
                                                    </div>
                                                </div>
                                            @endif -->
                                            <div>
                                                <label class="control-label" style="padding-right: 10px;">Partner</label>
                                                <div style="display: inline-block;">
                                                    @include('admin.parts.user-field',[
                                                        'key' => 'is_partner',
                                                        'info' => $fields['is_partner']
                                                    ])
                                                </div>
                                            </div>
                                        @endif
                                        <div>
                                            <label class="control-label" style="padding-right: 10px;">{{ !$item->is_dentist ? 'User' : '' }} ID</label>
                                            <div style="display: inline-block;">
                                                {{ Form::text( 'id', $item->id, array('class' => 'form-control', 'disabled', 'style' => 'max-width: 70px;' ) ) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Name</label>
                                <div class="col-md-10">
                                    @include('admin.parts.user-field',[
                                        'key' => 'name',
                                        'info' => $fields['name']
                                    ])
                                    @if(!empty($item->invited_himself_reg))
                                        <span style="color: red;">Duplicated email</span>
                                    @endif
                                </div>
                            </div>
                            @if($duplicated_names->isNotEmpty())
                                <p style="color: red;" class="col-md-10 col-md-offset-2">User/s with this name already exists:</p>
                                @foreach($duplicated_names as $dn)
                                    <p style="color: red;" class="col-md-10 col-md-offset-2">{{ $loop->iteration }}. <a href="{{ url('cms/users/edit/'.$dn->id) }}">{{ $dn->name }}</a></p>
                                @endforeach
                            @endif
                            @if(!$item->is_dentist && !empty($item->user_patient_type))
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Type</label>
                                    <div class="col-md-10">
                                        @include('admin.parts.user-field',[
                                            'key' => 'user_patient_type',
                                            'info' => $fields['user_patient_type']
                                        ])
                                    </div>
                                </div>
                            @endif
                            @if($item->is_dentist && !$item->is_clinic)
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Title</label>
                                    <div class="col-md-10">
                                        @include('admin.parts.user-field',[
                                            'key' => 'title',
                                            'info' => $fields['title']
                                        ])
                                    </div>
                                </div>
                            @endif
                            @if($item->is_clinic && !empty($item->worker_name))
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Worker Name</label>
                                    <div class="col-md-10">
                                        @include('admin.parts.user-field',[
                                            'key' => 'worker_name',
                                            'info' => $fields['worker_name']
                                        ])
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Working Position</label>
                                    <div class="col-md-10">
                                        @include('admin.parts.user-field',[
                                            'key' => 'working_position',
                                            'info' => $fields['working_position']
                                        ])
                                    </div>
                                </div>
                                @if(!empty($item->working_position) && $item->working_position == 'other')
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Other Working Position</label>
                                        <div class="col-md-10">
                                            @include('admin.parts.user-field',[
                                                'key' => 'working_position_label',
                                                'info' => $fields['working_position_label']
                                            ])
                                        </div>
                                    </div>
                                @endif
                            @endif
                            @if($item->is_dentist)
                                <div class="form-group">
                                    <label class="col-md-2 control-label">TRP Url</label>
                                    <div class="col-md-10" style="display: flex; align-items: center;">
                                        <span>https://reviews.dentacoin.com/en/dentist/</span>
                                        <input class="form-control" id="user-slug" disabled="disabled" name="slug" type="text" value="{{ $item->slug }}" style="flex: 1;height: 30px;margin-left: 2px;">
                                        <a href="javascript:;" class="btn btn-sm btn-primary" id="edit-slug" style="margin-left: 2px;">Edit</a>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <label class="col-md-2 control-label">Gender</label>
                                <div class="col-md-10">
                                    @include('admin.parts.user-field',[
                                        'key' => 'gender',
                                        'info' => $fields['gender']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Birth Year</label>
                                <div class="col-md-10">
                                    @include('admin.parts.user-field',[
                                        'key' => 'birthyear',
                                        'info' => $fields['birthyear']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Email</label>
                                <div class="col-md-10">
                                    @include('admin.parts.user-field',[
                                        'key' => 'email',
                                        'info' => $fields['email']
                                    ])
                                </div>
                            </div>
                            @if($duplicated_mails->isNotEmpty())
                                <p style="color: red;" class="col-md-10 col-md-offset-2">User/s with this email already exists:</p>
                                @foreach($duplicated_mails as $dm)
                                    <p style="color: red;" class="col-md-10 col-md-offset-2">{{ $loop->iteration }}. <a href="{{ url('cms/users/edit/'.$dm->id) }}">{{ $dm->name }}</a></p>
                                @endforeach
                            @endif
                            <div class="form-group">
                                <label class="col-md-2 control-label">Public Email</label>
                                <div class="col-md-10">
                                    @include('admin.parts.user-field',[
                                        'key' => 'email_public',
                                        'info' => $fields['email_public']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Phone</label>
                                <div class="col-md-10">
                                    @include('admin.parts.user-field',[
                                        'key' => 'phone',
                                        'info' => $fields['phone']
                                    ])
                                </div>
                            </div>

                            <div class="address-suggester-wrapper">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Country</label>
                                    <div class="col-md-10">
                                        <select name="country_id" class="form-control country-select">
                                            <option></option>
                                            @foreach( $countries as $country )
                                                <option value="{{ $country->id }}" code="{{ $country->code }}" {!! !empty($item->country_id) && $item->country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">State</label>
                                    <div class="col-md-10">
                                        @include('admin.parts.user-field',[
                                            'key' => 'state_name',
                                            'info' => $fields['state_name']
                                        ])
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">City</label>
                                    <div class="col-md-10">
                                        @include('admin.parts.user-field',[
                                            'key' => 'city_name',
                                            'info' => $fields['city_name']
                                        ])
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">ZIP code</label>
                                    <div class="col-md-10">
                                        @include('admin.parts.user-field',[
                                            'key' => 'zip',
                                            'info' => $fields['zip']
                                        ])
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Dental Practice</label>
                                    <div class="col-md-10">
                                        {{ Form::text( 'address', $item->address, array('class' => 'form-control address-suggester', 'autocomplete' => 'off' )) }}
                                    </div>
                                </div>

                                <div>
                                    <div class="suggester-map-div" style="height: 200px; display: none; margin: 10px 0px; background: transparent;">
                                    </div>
                                    <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px 20px;">
                                        {!! nl2br(trans('trp.common.check-address')) !!}
                                    </div>
                                    <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: -10px 0px 10px;">
                                        {!! nl2br(trans('trp.common.invalid-address')) !!}
                                    </div>
                                    <div class="alert alert-warning different-country-hint mobile" style="display: none; margin: -10px 0px 10px;">
                                        Unable to proceed. Please, choose address from selected country.
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Website / FB URL</label>
                                <div class="col-md-{{ $item->is_dentist ? '7' : '10' }}" >
                                    @include('admin.parts.user-field',[
                                        'key' => 'website',
                                        'info' => $fields['website']
                                    ])
                                </div>
                                @if($item->is_dentist)
                                    <label class="col-md-2 control-label user-l" style="padding-left: 0px;">Unsubscribed</label>
                                    <div class="col-md-1" style="padding-left: 0px;">
                                        @include('admin.parts.user-field',[
                                            'key' => 'unsubscribe',
                                            'info' => $fields['unsubscribe']
                                        ])
                                    </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Facebook ID</label>
                                <div class="col-md-7">
                                    @include('admin.parts.user-field',[
                                        'key' => 'fb_id',
                                        'info' => $fields['fb_id']
                                    ])
                                </div>                                
                                <label class="col-md-2 control-label user-l" style="padding-left: 0px;">FB Recommend</label>
                                <div class="col-md-1" style="padding-left: 0px;">
                                    @include('admin.parts.user-field',[
                                        'key' => 'fb_recommendation',
                                        'info' => $fields['fb_recommendation']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Civic ID</label>
                                <div class="col-md-7">
                                    @include('admin.parts.user-field',[
                                        'key' => 'civic_id',
                                        'info' => $fields['civic_id']
                                    ])
                                </div>
                                <label class="col-md-2 control-label user-l">Civic photo ID?</label>
                                <div class="col-md-1" style="padding-left: 0px;">
                                    @include('admin.parts.user-field',[
                                        'key' => 'civic_kyc',
                                        'info' => $fields['civic_kyc']
                                    ])
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">DCN Address</label>
                                <div class="col-md-7">
                                    @if($item->wallet_addresses->isNotEmpty())
                                        @foreach($item->wallet_addresses as $wa)
                                            <input type="text" name="dcn_address" class="form-control" value="{{ $wa->dcn_address }}" disabled="disabled"> <br/>

                                            @if(App\Models\WalletAddress::where('user_id', '!=', $item->id)->where('dcn_address', 'LIKE', $wa->dcn_address)->get()->isNotEmpty())

                                                <p style="color: red;" class="col-md-12">⇧ User/s with this dcn address already exists:</p>
                                                @foreach(App\Models\WalletAddress::where('user_id', '!=', $item->id)->where('dcn_address', 'LIKE', $wa->dcn_address)->get() as $dw)
                                                    <p style="color: red;" class="col-md-12">{{ $loop->iteration }}. <a href="{{ url('cms/users/edit/'.$dw->user_id) }}">{{ App\Models\User::withTrashed()->find($dw->user_id)->name }}</a></p>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @else
                                        <input type="text" name="dcn_address" class="form-control" disabled="disabled">
                                    @endif
                                </div>
                                <label class="col-md-2 control-label user-l" style="padding-left: 0px;">Allow withdraw</label>
                                <div class="col-md-1" style="padding-left: 0px;">
                                    @include('admin.parts.user-field',[
                                        'key' => 'allow_withdraw',
                                        'info' => $fields['allow_withdraw']
                                    ])
                                </div>
                            </div>
                            @if($item->is_dentist)
                                <div class="form-group">
                                    <label class="col-md-2 control-label">New Password</label>
                                    <div class="col-md-7">
                                        @include('admin.parts.user-field',[
                                            'key' => 'password',
                                            'info' => $fields['password']
                                        ])
                                    </div>
                                    <label class="col-md-2 control-label user-l" style="padding-left: 0px;">Use Hub App</label>
                                    <div class="col-md-1" style="padding-left: 0px;">
                                        @include('admin.parts.user-field',[
                                            'key' => 'is_hub_app_dentist',
                                            'info' => $fields['is_hub_app_dentist']
                                        ])
                                    </div>
                                </div>
                            @endif
                            
                        </div>
                        <div class="col-md-6">
                            <div class="form-group avatar-group">
                                <label class="col-md-6 control-label">Profile photo</label>
                                <div class="col-md-6">
                                    <label for="add-avatar" class="image-label" style="background-image: url('{{ $item->getImageUrl(true)}}');">
                                        <div class="loader">
                                            <i class="fas fa-circle-notch fa-spin"></i>
                                        </div>
                                        <input type="file" name="image" id="add-avatar" upload-url="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/addavatar') }}">
                                    </label>
                                    <a class="btn btn-primary delete-avatar" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/deleteavatar') }}" onclick="return confirm('{{ trans('admin.common.sure') }}')">
                                        <i class="fa fa-remove"></i> {{ trans('admin.page.'.$current_page.'.delete-avatar') }}
                                    </a>
                                </div>

                                <!-- <label class="col-md-6 control-label">Profile photo</label>
                                <div class="col-md-6">
                                    @include('admin.parts.user-field',[
                                        'key' => 'avatar',
                                        'info' => $fields['avatar']
                                    ])
                                </div> -->

                                @if($item->is_dentist)
                                    <div class="ratings">
                                        <div class="stars">
                                            <div class="bar" style="width: {{ $item->avg_rating/5*100 }}%;"></div>
                                        </div>
                                        <span class="rating">
                                            ({{ intval($item->ratings) }} reviews)
                                        </span>
                                        <div style="margin-top: 10px;">Average rating: {{ !empty($item->avg_rating) ? $item->avg_rating : 'N/A' }}</div>
                                        <a class="open-trp-link" target="_blank" href="{{ 'https://reviews.dentacoin.com'.explode('.com', $item->getLink())[1]}}">Open TRP Profile</a>
                                    </div>
                                @endif
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
                            @if($item->self_deleted_at)
                                <div class="form-group">
                                    <div class="col-md-12" style="text-align: right;">
                                        <span style="color: black; padding-right: 5px;">Self deleted at: {{ $item->self_deleted_at->toDateTimeString() }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <div class="col-md-12" style="text-align: right;">
                                    <span style="color: black;">Registered at: {{ $item->created_at->toDateTimeString() }}</span><br/>
                                </div>
                            </div>
                            @if($item->actions->isNotEmpty())
                                <div class="form-group">
                                    <div class="col-md-12" style="text-align: right;">
                                        @foreach($item->actions as $act)
                                            <span style="color: {{ $act->action == 'deleted' ? 'red' : 'black' }};"><span style="text-transform: capitalize;">{{ $act->action == 'restored_self_deleted' ? 'Self Deleted Restored' : $act->action }}</span> at: {{ $act->actioned_at->toDateTimeString() }}</span><br/>
                                            <span style="color: {{ $act->action == 'deleted' ? 'red' : 'black' }};">Reason: {{ $act->reason }}</span><br/><br/>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif(!empty($item->deleted_at))
                                <div class="form-group">
                                    <div class="col-md-12" style="text-align: right;">
                                        <span style="color: red;">Deleted at: {{ $item->deleted_at->toDateTimeString() }}</span><br/><br/>
                                    </div>
                                </div>
                            @endif
                            @if(!empty($item->invited_by) && $item->is_dentist)
                                <div class="form-group" style="text-align: right;">
                                    <div class="col-md-12">
                                        @if(!empty($item->platform) && $item->platform == 'trp')
                                            @if($item->id <= 79174)
                                                Added by patient <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->name }}</a>
                                            @else
                                                @if($item->invited_from_form)
                                                    Added by patient <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->name }}</a>
                                                @else
                                                    @if($item->is_dentist)
                                                        @if(App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->is_clinic)
                                                            Added by clinic at {{ config('platforms')[$item->platform]['name'] }} - <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->name }}</a>
                                                        @else
                                                            Added by dentist at {{ config('platforms')[$item->platform]['name'] }} signup <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->name }}</a>
                                                        @endif
                                                    @else
                                                        Registered from {{ config('platforms')[$item->platform]['name'] }} friend invite <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->name }}</a>
                                                    @endif
                                                @endif
                                            @endif
                                        @else
                                            Registered from {{ !empty($item->platform) ? config('platforms')[$item->platform]['name'] : '' }} friend invite <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $item->invited_by)->withTrashed()->first()->name }}</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <a href="{{ url('cms/users/loginas/'.$item->id) }}" target="_blank" class="btn btn-sm btn-primary form-control user-b"> {{ trans('admin.page.profile.loginas') }} </a>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <a href="{{ url('cms/users/user-data/'.$item->id) }}" target="_blank" class="btn btn-sm btn-warning form-control user-b">Export Personal Data</a>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    @if(!empty($item->deleted_at))
                                        <a class="btn btn-sm btn-info form-control user-b" href="javascript:;" data-toggle="modal" data-target="#restoreModal">
                                            Restore
                                        </a>
                                    @else
                                        <a class="btn btn-sm btn-danger form-control user-b" href="javascript:;" data-toggle="modal" data-target="#deleteModal">
                                            Delete
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @if($item->self_deleted)
                                <div class="form-group">
                                    <div class="col-md-6"></div>
                                    <div class="col-md-6">
                                        <a href="javascript:;" data-toggle="modal" data-target="#restoreSelfDeletedModal" class="btn btn-sm btn-info form-control user-b">
                                            Restore Self Deleted
                                        </a>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control user-b"> {{ trans('admin.common.save') }} </button>
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
    <div class="row with-dropdown">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading toggle-button">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-photos') }}</h4>
                </div>
                <div class="panel-body toggled-area">
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

<h4 style="margin-bottom: 20px;">TRUSTED REVIEWS</h4>
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
                            'created_at'        => array('format' => 'datetime','width' => '20%'),
                            'user'              => array('template' => 'admin.parts.table-reviews-user', 'width' => '30%'),
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

    @if($item->is_clinic)
        @if($item->team->isNotEmpty())

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-inverse">
                        <div class="panel-heading">
                            <h4 class="panel-title">Clinic's Team</h4>
                        </div>
                        <div class="panel-body">
                            <div class="panel-body">
                                <div class="row">

                                    <table class="table table-striped table-question-list">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Dentist</th>
                                                <th>Job</th>
                                                <th>Is approved?</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->team as $team)
                                                <tr>
                                                    <td>
                                                        {{ $team->created_at ? $team->created_at->toDateTimeString() : '' }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ url('/cms/users/edit/'.$team->clinicTeamWithTrashed->id) }}">
                                                            {{ $team->clinicTeamWithTrashed->getName() }} {{ $team->clinicTeamWithTrashed->deleted_at ? '(deleted)' : '' }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        Dentist
                                                    </td>
                                                    <td>
                                                        {!! $team->approved ? '<span class="label label-success">'.trans('admin.common.yes').'</span>' : '<span class="label label-warning">'.trans('admin.common.no').'</span>' !!}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @foreach($item->invites_team_unverified as $team_invited)
                                                <tr>
                                                    <td>
                                                        {{ $team_invited->created_at->toDateTimeString() }}
                                                    </td>
                                                    <td>
                                                        {{ $team_invited->invited_name }}
                                                    </td>
                                                    <td>
                                                        {{ !empty($team_invited->job) ? config('trp.team_jobs')[$team_invited->job] : '' }}
                                                    </td>
                                                    <td>
                                                        <span class="label label-success">Yes</span>
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
            </div>
        @endif
    @else
        @if($item->my_workplace->isNotEmpty())
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-inverse">
                        <div class="panel-heading">
                            <div class="panel-heading-btn">
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                            </div>
                            <h4 class="panel-title">Workplace</h4>
                        </div>
                        <div class="panel-body">
                            @include('admin.parts.table', [
                                'table_id' => 'team',
                                'table_fields' => [
                                    'created_at'        => array('format' => 'datetime','width' => '20%'),
                                    'clinic'              => array('template' => 'admin.parts.table-team-clinic', 'width' => '30%'),
                                    'approved'              => array('format' => 'bool'),
                                ],
                                'table_subpage' => 'my_workplace',
                                'table_data' => $item->my_workplace,
                                'table_pagination' => false,
                                'pagination_link' => array()
                            ])
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

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
                            'created_at'        => array('format' => 'datetime','width' => '20%'),
                            'link'              => array('template' => 'admin.parts.table-reviews-link','width' => '30%'),
                            'dentist'           => array('template' => 'admin.parts.table-reviews-dentist', 'label' => 'Dentist reviewed'),
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

    @if(!empty($item->patient_invites_dentist->isNotEmpty()))
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        <div class="panel-heading-btn">
                            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        </div>
                        <h4 class="panel-title"> Added dentists </h4>
                    </div>
                    <div class="panel-body">
                        @include('admin.parts.table', [
                            'table_id' => 'dentist-invited',
                            'table_fields' => [
                                'created_at'        => array('format' => 'datetime','width' => '20%'),
                                'dentist'           => array('template' => 'admin.parts.table-added-dentist', 'label' => 'Dentist'),
                                'status'           => array('template' => 'admin.parts.table-added-dentist-status', 'label' => 'Status'),
                            ],
                            'table_subpage' => 'reviews',
                            'table_data' => $item->patient_invites_dentist,
                            'table_pagination' => false,
                            'pagination_link' => array()
                        ])
                    </div>
                </div>
            </div>
        </div>
    @endif

@endif

@if($item->vox_surveys_and_polls->isNotEmpty())
    <h4 style="margin-bottom: 20px;">DENTAVOX</h4>
    <div class="row show-hide-section">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading show-hide-button">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-vox-rewards') }} </h4>
                </div>
                <div class="panel-body show-hide-area">
                    <span class="total-num">Total number: {{ count($item->vox_surveys_and_polls) }}</span>
                    @include('admin.parts.table', [
                        'table_id' => 'vox-rewards',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime', 'width' => '20%'),
                            'seconds'              => array('template' => 'admin.parts.table-vox-rewards-duration', 'width' => '30%'),
                            'vox_id'              => array('template' => 'admin.parts.table-vox-rewards-user', 'width' => '20%'),
                            'device'          => array('template' => 'admin.parts.table-logins-device', 'width' => '20%'),
                            'reward'           => array('width' => '100%'),
                            'delete'              => array('template' => 'admin.parts.table-vox-rewards-delete'),
                        ],
                        'table_data' => $item->vox_surveys_and_polls,
                        'table_subpage' => 'vox-rewards',
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
                <div class="button-wrapper">
                    <a class="show-all-button btn btn-primary" href="javascript:;">Show all surveys taken</a>
                </div>
            </div>
        </div>
    </div>
@endif

@if($unfinished)
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
                            'taken_date'        => array('format' => 'datetime', 'width' => '20%'),
                            'title'              => array('width' => '30%'),
                            'device'                => array('template' => 'admin.parts.table-unfinished-device', 'width' => '100%'),
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

@if($habits_test_ans)
    <div class="row" id="habits-list">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Demographics & Habits</h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'vox-habits',
                        'table_fields' => [
                            'question'          => array('label' => 'Question'),
                            'old_answer'          => array('label' => 'Initial Answer'),
                            'answer'          => array('label' => 'Updated Answer'),
                            'last_updated'      => array('label' => 'Last Updated'),
                            'updates_count'      => array('label' => 'Updates'),
                        ],
                        'table_data' => $habits_tests,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
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

<h4 style="margin-bottom: 20px;">Activity History</h4>

@if($item->history->isNotEmpty())
    <div class="row show-hide-section">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading show-hide-button">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title-transactions') }}</h4>
                </div>
                <div class="panel-body show-hide-area">
                    <span class="total-num">Total number: {{ count($item->history) }}</span>
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
                <div class="button-wrapper">
                    <a class="show-all-button btn btn-primary" href="javascript:;">Show all transactions</a>
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

@if($emails)
    <div class="row show-hide-section" id="logins-list">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading show-hide-button">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Notifications Sent</h4>
                </div>
                <div class="panel-body show-hide-area">
                    @include('admin.parts.table', [
                        'table_id' => 'user-emails',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime','width' => '20%'),
                            'template_id'       => array('template' => 'admin.parts.table-email-template','width' => '100%'),
                            'platform'       => array('label' => 'Platform', 'template' => 'admin.parts.table-platforms'),
                        ],
                        'table_data' => $emails,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
                <div class="button-wrapper">
                    <a class="show-all-button btn btn-primary" href="javascript:;">Show all notifications</a>
                </div>
            </div>
        </div>
    </div>
@endif

@if($item->logins->isNotEmpty())
    <div class="row with-limits show-hide-section" id="logins-list">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading show-hide-button">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">User Logins</h4>
                </div>
                <div class="panel-body show-hide-area">
                    <!-- <div class="limit-buttons">
                        <span>Show last: </span>
                        <a href="javascript:;" limit="50">50</a>
                        <a href="javascript:;" limit="100">/ 100</a>
                    </div> -->
                    @include('admin.parts.table', [
                        'table_id' => 'vox-user-logins',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime','width' => '20%'),
                            'ip'                => array('template' => 'admin.parts.table-logins-user','width' => '30%'),
                            'country'          => array('label' => 'Country','width' => '30%'),
                            'device'          => array('template' => 'admin.parts.table-logins-device','width' => '100%'),
                            'platform'          => array('template' => 'admin.parts.table-platforms'),
                        ],
                        'table_data' => $item->logins,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
                <div class="button-wrapper">
                    <a class="show-all-button btn btn-primary" href="javascript:;">Show all user logins</a>
                </div>
            </div>
        </div>
    </div>
@endif

@if($item->claims->isNotEmpty())
    <div class="row with-limits" id="claims-list">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Profile Claims</h4>
                </div>
                <div class="panel-body">
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'dentist-claims',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime','width' => '10%'),
                            'name'              => array('label' => 'Name','width' => '10%'),
                            'email'              => array('label' => 'Email','width' => '10%'),
                            'phone'             => array('label' => 'Phone','width' => '10%'),
                            'job'               => array('label' => 'Job','width' => '10%'),
                            'explain_related'   => array('label' => 'Explain','width' => '20%'),
                            'status'            => array('width' => '10%'),
                            'from_mail'           => array('width' => '10%', 'template' => 'admin.parts.table-dentist-claim-from'),
                            'update'            => array('label' => 'Actions', 'template' => 'admin.parts.table-dentist-claim-edit','width' => '10%'),
                        ],
                        'table_data' => $item->claims,
                        'table_pagination' => false,
                    ])
                </div>
            </div>
            </div>
        </div>
    </div>
@endif

@if($item->recommendations->isNotEmpty())
    <div class="row with-limits" id="recommendations-list">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Recommendations</h4>
                </div>
                <div class="panel-body">
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'recommendations',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime','width' => '20%'),
                            'scale'              => array('label' => 'Scale','width' => '20%'),
                            'description'              => array('label' => 'Comment','width' => '100%'),
                        ],
                        'table_data' => $item->recommendations,
                        'table_pagination' => false,
                    ])
                </div>
            </div>
            </div>
        </div>
    </div>
@endif

@if($item->is_dentist && $item->dentist_fb_page->isNotEmpty())
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Facebook Page Tabs</h4>
                </div>
                <div class="panel-body">
                    <div class="panel-body">
                        <div class="row">

                            <table class="table table-striped table-question-list">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->dentist_fb_page as $dpt)
                                        <tr>
                                            <td>
                                                {{ $dpt->created_at->toDateTimeString() }}
                                            </td>
                                            <td>
                                                <a href="https://www.facebook.com/profile.php?id={{ $dpt->fb_page }}" target="_blank">{{ $dpt->fb_page }}</a>
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
    </div>
@endif

<div id="deleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Delete user</h4>
            </div>
            <div class="modal-body">
                <form action="{{ url('cms/users/delete/'.$item->id) }}" method="post">
                    @if($item->platform == 'external')
                        <div class="external-patients-wrap">
                            <p style="color: red;">You are about to delete Dentaprimes patient. Are you sure you want to continue? This type of users are using the Hub App application.</p>
                            <div style="text-align: center;">
                                <a href="javascript:;" class="btn btn-success external-patients-button" style="margin-right: 20px;">Yes</a><a href="javascript:;" class="btn btn-primary" data-dismiss="modal">No</a>
                            </div>
                        </div>
                    @endif
                    <div class="reason-wrap" style="{!! $item->platform == 'external' ? 'display: none;' : ''; !!}">
                        <textarea class="form-control" name="deleted_reason" placeholder="Write the reason why you want to delete this user"></textarea>
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Delete</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="restoreModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Restore user</h4>
            </div>
            <div class="modal-body">
                <form action="{{ url('cms/users/restore/'.$item->id) }}" method="post">
                    <textarea class="form-control" name="restored_reason" placeholder="Write the reason why you want to restore this user"></textarea>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Restore</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="restoreSelfDeletedModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Restore self deleted user</h4>
            </div>
            <div class="modal-body">
                <form action="{{ url('cms/users/restore-self-deleted/'.$item->id) }}" method="post">
                    <textarea class="form-control" name="restored_reason" placeholder="Write the reason why you want to restore this user"></textarea>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Restore</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<style type="text/css">
    tr {
        font-size: 12px;
    }
</style>

@endsection