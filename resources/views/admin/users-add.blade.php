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
                {!! Form::open(array('url' => url('cms/'.$current_page.'/add'), 'method' => 'post', 'class' => 'form-horizontal','files' => true)) !!}
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
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <label for="add-avatar" class="image-label">
                                    <div class="centered-hack">
                                        <p style="text-align: center;color: black;width: 100%;margin-bottom: 0px;">
                                            {!! nl2br(trans('trp.popup.popup-register.add-photo')) !!}                                                  
                                        </p>
                                    </div>
                                    <input type="file" name="image" id="add-avatar">
                                </label>
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