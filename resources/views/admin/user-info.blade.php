<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
    <head>
        <base href="{{ url('/cms/') }}" >
        <meta charset="utf-8" />
        <meta name="robots" content="noindex">
        <title>{{ trans('admin.page.'.$current_page.'.title') }} - {{ trans('admin.title') }}</title>
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        
        <!-- ================== BEGIN BASE CSS STYLE ================== -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
        <link href="admin/plugins/jquery-ui/themes/base/minified/jquery-ui.min.css" rel="stylesheet" />
        <link href="admin/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
        <link href="admin/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
        <link href="admin/css/animate.min.css" rel="stylesheet" />
        <link href="admin/css/style.min.css" rel="stylesheet" />
        <link href="admin/css/style-responsive.min.css" rel="stylesheet" />
        <link href="admin/css/theme/default.css" rel="stylesheet" id="theme" />
        <!-- ================== END BASE CSS STYLE ================== -->
        
        <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
        <link href="admin/plugins/jquery-jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" />
        <link href="admin/plugins/bootstrap-datepicker/css/datepicker.css" rel="stylesheet" />
        <link href="admin/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" />
        <link href="admin/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
        <link href="admin/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
        <link href="admin/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" />
        <link href="admin/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet" />
        <link href="admin/plugins/select2/dist/css/select2.min.css" rel="stylesheet"/>
        
        <link href="admin/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" />
        <link href="admin/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet" />
        <link href="admin/plugins/jquery-file-upload/css/jquery.fileupload-ui.css" rel="stylesheet" />

        <link href="admin/plugins/isotope/isotope.css" rel="stylesheet" />
        <link href="admin/plugins/lightbox/css/lightbox.css" rel="stylesheet" />
        

        <!-- ================== END PAGE LEVEL STYLE ================== -->


        <link href="admin/css/custom.css?ver={{ $cache_version }}" rel="stylesheet" />
        
        <!-- ================== BEGIN BASE JS ================== -->
        <script src="admin/plugins/pace/pace.min.js"></script>
        <!-- ================== END BASE JS ================== -->
    </head>
    <body>
        <div class="row">
		    <!-- begin col-6 -->
		    <div class="col-md-12">
            	<div class="row">
                    <div class="col-sm-6">
		                <div class="form-group clearfix">
		                    <label class="col-md-2 control-label">Name</label>
		                    <div class="col-md-10">
		                        <p>{{ $user->name }}</p>
		                    </div>
		                </div>

		                <div class="clearfix" style="margin-bottom: 20px;">
			                @if($duplicated_names->isNotEmpty())
	                            <p style="color: red;" class="col-md-10 col-md-offset-2">User/s with this name already exists:</p>
	                            @foreach($duplicated_names as $dn)
	                                <p style="color: red;" class="col-md-10 col-md-offset-2">{{ $loop->iteration }}. <a href="{{ url('cms/users/edit/'.$dn->id) }}">{{ $dn->name }} {{ $dn->is_dentist ? '('.config('user-statuses')[$dn->status].($dn->deleted_at ? ', Deleted' : '').')' : '' }}</a></p>
	                            @endforeach
	                        @endif
	                    </div>

		                <div class="form-group clearfix" style="margin-bottom: 20px;">
		                    <label class="col-md-2 control-label">Website</label>
		                    <div class="col-md-10">
		                    	@if($user->website)
			                    	@if(filter_var($user->website, FILTER_VALIDATE_URL) === FALSE)
	                                    <p>{{ $user->website }}</p>
	                                @else
	                                    <p><a href="{{ $user->website }}" target="_blank">{{ $user->website }}</a></p>
	                                @endif
	                            @else
	                            	<p>-</p>
	                            @endif
		                    </div>
		                </div>
		            </div>
		            <div class="col-sm-6">
		            	<div class="form-group clearfix">
		                    <label class="col-md-2 control-label"></label>
		                    <div class="col-md-10" style="text-align: right;">
		                        <img src="{{ $user->getImageUrl(true) }}" style="max-width: 100%;">
		                    </div>
		                </div>

		                <div class="form-group">
                            <div class="col-md-12" style="text-align: right;">
                                <span style="color: black; padding-right: 5px;">Self deleted:</span>
                                @if($user->self_deleted)
                                    <span style="color: red; font-weight: bold; font-size: 16px;">
                                        Yes
                                    </span>
                                @else
                                    <span style="color: black;">No</span>
                                @endif
                            </div>
                        </div>
                        @if($user->self_deleted_at)
                            <div class="form-group">
                                <div class="col-md-12" style="text-align: right;">
                                    <span style="color: black; padding-right: 5px;">Self deleted at: {{ $user->self_deleted_at->toDateTimeString() }}</span>
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <div class="col-md-12" style="text-align: right;">
                                @if(!empty($user->platform))
                                    <span style="color: black;">Registered platform: {{ config('platforms')[$user->platform]['name'] }}</span><br/><br/>
                                @endif
                                <span style="color: black;">Registered at: {{ $user->created_at->toDateTimeString() }}</span><br/>
                            </div>
                        </div>
                        @if($user->actions->isNotEmpty())
                            <div class="form-group">
                                <div class="col-md-12" style="text-align: right;">
                                    @foreach($user->actions as $act)
                                        <span style="color: {{ $act->action == 'deleted' || $act->action == 'bad_ip' || $act->action == 'suspicious_admin' ? 'red' : 'black' }};"><span style="text-transform: capitalize;">{{ $act->action == 'restored_self_deleted' ? 'Self Deleted Restored' : ($act->action == 'bad_ip' ? 'Bad IP' : ($act->action == 'suspicious_admin' ? 'Suspicious' : $act->action)) }}</span> at: {{ $act->actioned_at->toDateTimeString() }}</span><br/>
                                        <span style="color: {{ $act->action == 'deleted' || $act->action == 'bad_ip' || $act->action == 'suspicious_admin' ? 'red' : 'black' }};">Reason: {{ $act->reason }}</span><br/><br/>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(!empty($user->deleted_at))
                            <div class="form-group">
                                <div class="col-md-12" style="text-align: right;">
                                    <span style="color: red;">Deleted at: {{ $user->deleted_at->toDateTimeString() }}</span><br/><br/>
                                </div>
                            </div>
                        @endif
                        @if(!empty($user->invited_by) && $user->is_dentist)
                            <div class="form-group" style="text-align: right;">
                                <div class="col-md-12">
                                    @if(!empty($user->platform) && $user->platform == 'trp')
                                        @if($user->id <= 79174)
                                            Added by patient <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->name }}</a>
                                        @else
                                            @if($user->invited_from_form)
                                                Added by patient <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->name }}</a>
                                            @else
                                                @if($user->is_dentist)
                                                    @if(App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->is_clinic)
                                                        Added by clinic at {{ config('platforms')[$user->platform]['name'] }} - <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->name }}</a>
                                                    @else
                                                        Added by dentist at {{ config('platforms')[$user->platform]['name'] }} signup <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->name }}</a>
                                                    @endif
                                                @else
                                                    Registered from {{ config('platforms')[$user->platform]['name'] }} friend invite <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->name }}</a>
                                                @endif
                                            @endif
                                        @endif
                                    @else
                                        Registered from {{ !empty($user->platform) ? config('platforms')[$user->platform]['name'] : '' }} friend invite <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->name }}</a>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if($user->invited_by===0 && $user->is_dentist)
                            <div class="form-group" style="text-align: right;">
                                <div class="col-md-12">
                                    Added by not registered patient
                                </div>
                            </div>
                        @endif
                        @if(!empty($user->invited_by) && !$user->is_dentist)
                            <div class="form-group" style="text-align: right;">
                                <div class="col-md-12">
                                    Registered from {{ !empty($user->platform) ? config('platforms')[$user->platform]['name'] : '' }} friend invite <a href="{{ url('cms/users/edit/'.App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->id) }}">{{ App\Models\User::where('id', $user->invited_by)->withTrashed()->first()->name }}</a>
                                </div>
                            </div>
                        @endif
		            </div>
		        </div>
		        <!-- end panel -->
		    </div>
		</div>

		@if($user->logins->isNotEmpty())
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
		                        'table_data' => $user->logins,
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

		<style type="text/css">
		    tr {
		        font-size: 12px;
		    }

		    body, html {
		        background: white !important;
    			height: auto !important;
		    }
		</style>

<!-- ================== BEGIN BASE JS ================== -->
        <script src="admin/plugins/jquery/jquery-1.9.1.min.js"></script>
        <script src="admin/plugins/jquery/jquery-migrate-1.1.0.min.js"></script>
        <script src="admin/plugins/jquery-ui/ui/minified/jquery-ui.min.js"></script>
        <script src="admin/plugins/bootstrap/js/bootstrap.min.js"></script>
        <!--[if lt IE 9]>
            <script src="admin/crossbrowserjs/html5shiv.js"></script>
            <script src="admin/crossbrowserjs/respond.min.js"></script>
            <script src="admin/crossbrowserjs/excanvas.min.js"></script>
        <![endif]-->
        <script src="admin/plugins/slimscroll/jquery.slimscroll.min.js"></script>
        <script src="admin/plugins/jquery-cookie/jquery.cookie.js"></script>
        <!-- ================== END BASE JS ================== -->
        
        <!-- ================== BEGIN PAGE LEVEL JS ================== -->
        <script src="admin/plugins/gritter/js/jquery.gritter.js"></script>
        <script src="admin/plugins/flot/jquery.flot.min.js"></script>
        <script src="admin/plugins/flot/jquery.flot.time.min.js"></script>
        <script src="admin/plugins/flot/jquery.flot.resize.min.js"></script>
        <script src="admin/plugins/flot/jquery.flot.pie.min.js"></script>
        <script src="admin/plugins/sparkline/jquery.sparkline.js"></script>
        <script src="admin/plugins/jquery-jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="admin/plugins/jquery-jvectormap/jquery-jvectormap-world-mill-en.js"></script>
        <script src="admin/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script src="admin/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

        <script src="admin/plugins/DataTables/media/js/jquery.dataTables.js"></script>
        <script src="admin/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
        <script src="admin/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
        <script src="admin/plugins/bootstrap-daterangepicker/moment.js"></script>
        <script src="admin/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
        <script src="admin/plugins/select2/dist/js/select2.min.js"></script>
        <script src="admin/plugins/bootstrap-eonasdan-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        <script src="admin/js/table-manage-responsive.demo.js"></script>
        <script src="admin/plugins/ckeditor/ckeditor.js"></script>

        <script src="admin/plugins/jstree/dist/jstree.min.js"></script>

        <script src="admin/plugins/lightbox/js/lightbox-2.6.min.js"></script>

        <script src="admin/js/apps.min.js"></script>
        <!-- ================== END PAGE LEVEL JS ================== -->

        <script>
            $(document).ready(function() {
                App.init();
                TableManageResponsive.init();
            });

            var current_page = "{{ $current_page }}";
            var current_subpage = "{{ $current_subpage }}";
            var confirm_sure = "{{ trans('admin.common.sure') }}";

            var langs = {};
            @foreach($langs as $code => $arr)
                langs['{{ $code }}'] = '{{ $arr['name'] }}';
            @endforeach
            var colors = [];
            @foreach(config('paddings.colors') as $code)
                colors.push('{{ $code }}');
            @endforeach
        </script>
        

        <!-- ================== CUSTOM JS ================== -->
        <script src="admin/js/ckeditor/ckeditor.js"></script>
        <script src="admin/js/custom/all.js?ver={{ $cache_version }}"></script>
        @if( !empty( config('admin.pages.'.$current_page.'.js') ) )
            @foreach ( config('admin.pages.'.$current_page.'.js') as $file )
                <script src="admin/js/custom/{{ $file }}?ver={{ $cache_version }}"></script>
            @endforeach
        @endif

        @if(!empty( config('admin.pages.'.$current_page.'.jscdn')) && is_array(config('admin.pages.'.$current_page.'.jscdn')))
            @foreach(config('admin.pages.'.$current_page.'.jscdn') as $jscdn)
                <script src="{{ $jscdn }}"></script>
            @endforeach
        @endif
        <!-- ================== END CUSTOM JS ================== -->
    </body>
</html>