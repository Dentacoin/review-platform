<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
    <head>
        <base href="{{ url('/cms/') }}" >
        <meta charset="utf-8" />
        <title>Admin</title>
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
	<div id="page-container" class="fade">
	    <!-- begin login -->
        <div class="login bg-black animated fadeInDown">
            <!-- begin brand -->
            <div class="login-header">
                <div class="brand">
                    <span class="logo"></span> Admin
                </div>
                <div class="icon">
                    <i class="fa fa-sign-in"></i>
                </div>
            </div>
            <!-- end brand -->
            <form method="post">
                {!! csrf_field() !!}
	            <div class="login-content">
                	@include('admin.errors')
	                <form action="index.html" method="POST" class="margin-bottom-0">
	                    <div class="form-group m-b-20">
	                        <input type="text" name="username" class="form-control input-lg" placeholder="Username" value="{{ old('username') }}" />
	                    </div>
	                    <div class="form-group m-b-20">
	                        <input type="password" name="password" class="form-control input-lg" placeholder="Password" />
	                    </div>
	                    <div class="checkbox m-b-20">
	                        <label>
	                            <input type="checkbox" name="remember" checked="checked" />Remember me
	                        </label>
	                    </div>
	                    <div class="login-buttons">
	                        <button type="submit" class="btn btn-success btn-block btn-lg">Login</button>
	                    </div>
	                </form>
	            </div>
	        </form>
        </div>
        <!-- end login -->
        
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
	<script src="admin/js/apps.min.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->
	
	<script>
		$(document).ready(function() {
			App.init();
		});
	</script>
</body>
</html>
