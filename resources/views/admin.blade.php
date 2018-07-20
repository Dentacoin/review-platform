@if(empty($is_ajax))

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
    <head>
        <base href="{{ url('/cms/') }}" >
        <meta charset="utf-8" />
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


        <link href="admin/css/custom.css" rel="stylesheet" />
        
        <!-- ================== BEGIN BASE JS ================== -->
        <script src="admin/plugins/pace/pace.min.js"></script>
        <!-- ================== END BASE JS ================== -->
    </head>
    <body>
        <!-- begin #page-loader -->
        <div id="page-loader" class="fade in"><span class="spinner"></span></div>
        <!-- end #page-loader -->
        
        <!-- begin #page-container -->
        <div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
            <!-- begin #header -->
            <div id="header" class="header navbar navbar-default navbar-fixed-top">
                <!-- begin container-fluid -->
                <div class="container-fluid">
                    <!-- begin mobile sidebar expand / collapse button -->
                    <div class="navbar-header">
                        <a href="{{ url('cms') }}" class="navbar-brand"><span class="navbar-logo"></span>Admin</a>
                        <button type="button" class="navbar-toggle" data-click="sidebar-toggled">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>
                    <!-- end mobile sidebar expand / collapse button -->
                    
                    <!-- begin header navigation right -->
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown navbar-user">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                                <span class="hidden-xs">{{ $admin->username }}</span>
                                <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu animated fadeInLeft">
                                <li class="arrow"></li>
                                <li><a href="{{ url('cms/logout') }}">{{ trans('admin.logout') }}</a></li>
                            </ul>
                        </li>
                    </ul>


                        

                    <!-- end header navigation right -->
                </div>
                <!-- end container-fluid -->
            </div>
            <!-- end #header -->
            
            <!-- begin #sidebar -->
            <div id="sidebar" class="sidebar">
                <!-- begin sidebar scrollbar -->
                <div data-scrollbar="true" data-height="100%">
                    <!-- end sidebar user -->
                    <!-- begin sidebar nav -->
                    <ul class="nav">
                        @foreach ( config('admin.pages') as $key => $page )
                            <li class="@if (!empty($page['subpages']))has-sub @endif @if ($key==$current_page)active @endif">
                                <a href="@if (!empty($page['subpages']))javascript:;@else{{ !empty($page['href']) ? url('cms/'.$page['href']) : url('cms/'.$key) }}@endif">
                                    <?php if(!empty($counters[$key]) ) { ?>
                                        <span class="badge pull-right">{{ $counters[$key] }}</span>
                                    <?php } ?>
                                    @if (!empty($page['subpages']))
                                        <b class="caret pull-right"></b>
                                    @endif
                                    <i class="fa fa-{{ $page['icon'] }}"></i>
                                    <span>{{ trans('admin.page.'.$key.'.title') }}</span>
                                </a>
                                @if (!empty($page['subpages']))
                                    <ul class="sub-menu">
                                        @foreach ( $page['subpages'] as $skey => $spage )
                                        <li @if ($key==$current_page && $skey==$current_subpage)class="active"@endif>
                                            <a href="{{ url('cms/'.$key.'/'.$skey.'/') }}" >
                                                {{ trans('admin.page.'.$key.'.'.$skey.'.title') }}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach

                        <!-- begin sidebar minify button -->
                        <li><a href="javascript:;" class="sidebar-minify-btn" data-click="sidebar-minify"><i class="fa fa-angle-double-left"></i></a></li>
                        <!-- end sidebar minify button -->
                    </ul>
                    <!-- end sidebar nav -->
                </div>
                <!-- end sidebar scrollbar -->
            </div>
            <div class="sidebar-bg"></div>
            <!-- end #sidebar -->
            
            <!-- begin #content -->
            <div id="content" class="content">
                @include('admin.errors')

@endif

                @yield('content')

@if(empty($is_ajax))

                <div class="modal modal-message fade in" id="modal-message">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4 class="modal-title"></h4>
                            </div>
                            <div class="modal-body">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <!-- end #content -->
            
            <!-- begin scroll to top btn -->
            <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
            <!-- end scroll to top btn -->
        </div>
        <!-- end page container -->
        
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
        <script src="admin/js/custom/all.js"></script>
        @if( !empty( config('admin.pages.'.$current_page.'.js') ) )
            @foreach ( config('admin.pages.'.$current_page.'.js') as $file )
                <script src="admin/js/custom/{{ $file }}"></script>
            @endforeach
        @endif
        <!-- ================== END CUSTOM JS ================== -->
    </body>
</html>

@endif