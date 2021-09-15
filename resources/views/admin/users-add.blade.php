@extends('admin')

@section('content')

<h1 class="page-header"> 
    Add Dentist
</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Add Dentist</h4>
            </div>
            <div class="panel-body">
                {!! Form::open(array('url' => url('cms/users/users/add'), 'method' => 'post', 'class' => 'form-horizontal','files' => true)) !!}
                    {!! csrf_field() !!}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-2 control-label">User Type</label>
                                <div class="col-md-10">
                                    <div class="flex" style="align-items: baseline;justify-content: space-between;">
                                        <div>
                                            {{ Form::select( 'type' , ['dentist' => 'Dentist', 'clinic' => 'Clinic'] , old('type') , array('class' => 'form-control')) }}
                                        </div>
                                        <div>
                                            <label class="control-label" style="padding-right: 10px;">Partner</label>
                                            <div style="display: inline-block;">
                                            	{{ Form::select( 'is_partner' , ['' => '-'] + $fields['is_partner']['values'], old('is_partner') , array('class' => 'form-control')) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Name</label>
                                <div class="col-md-10">
                                    {{ Form::text( 'name', old('name'), array('class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Title</label>
                                <div class="col-md-10">
                                    {{ Form::select( 'title' , ['' => '-'] + $fields['title']['values'], old('title') , array('class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Email</label>
                                <div class="col-md-10">
                                    {{ Form::text( 'email', old('email'), array('class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Phone</label>
                                <div class="col-md-10">
                                    {{ Form::text( 'phone', old('phone'), array('class' => 'form-control')) }}
                                </div>
                            </div>

                            <div class="address-suggester-wrapper">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Country</label>
                                    <div class="col-md-10">
                                        <select name="country_id" class="form-control country-select">
                                            <option></option>
                                            @foreach( $countries as $country )
                                                <option value="{{ $country->id }}" code="{{ $country->code }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Dental Practice</label>
                                    <div class="col-md-10">
                                        {{ Form::text( 'address', old('address'), array('class' => 'form-control address-suggester', 'autocomplete' => 'off' )) }}
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
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Website / FB URL</label>
                                <div class="col-md-10">
                                    {{ Form::text( 'website', old('website'), array('class' => 'form-control')) }}
                                </div>
                            </div> 
                            <div class="form-group">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control"> {{ trans('admin.common.save') }} </button>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col-md-6">
                            <label class="col-md-6 control-label">Add avatar</label>
                            <div class="col-md-6">
                                <div class="upload-image-wrapper">
                                    <label for="add-avatar" class="image-label">
                                        <input type="file" name="image" id="add-avatar" upload-url="{{ url('cms/users/users/upload-temp') }}">
                                        <input type="hidden" name="avatar" class="avatar">
                                    </label>
                                    
                                    <div class="cropper-container add-team-cropper"></div>
                                    <div class="avatar-name-wrapper">
                                        <span class="avatar-name"></span>
                                        <button class="destroy-croppie" type="button">×</button>
                                    </div>
        
                                    <div class="max-size-label">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="width-100">
                                            <path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z" class=""></path>
                                        </svg>
                                        {{ trans('trp.popup.add-branch.image-max-size') }}
                                    </div>
                                    <div class="alert alert-warning image-big-error" style="display: none; margin-top: 20px;">The file you selected is large. Max size: 2MB.</div>
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

@endsection