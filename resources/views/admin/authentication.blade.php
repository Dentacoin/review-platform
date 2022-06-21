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
                    <img src="{{ url('img/dc-logo.png') }}" width="30" style="display: inline-block;margin-top: -7px;margin-right:10px"/>Admin
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
	                <form method="POST" class="margin-bottom-0">
                        @if($qrCodeUrl)
                            <div style="text-align: center;">
                                <p style="color: white;">Scan this QR code with Google Authenticator:</p>
                                <br/>
                                <img src="{{ $qrCodeUrl }}"/>
                                <br/>
                                <br/>
                                <p style="color: white;">and enter the code:</p>
                            </div>
                        @else
                            <p style="color: white;">Open your Google Authenticator and enter the code:</p>
                        @endif
                        <div class="form-group m-b-20">
                            <input type="text" name="kyc_code" id="kyc_code" placeholder="Code" maxlength="16" class="form-control">
                        </div>
                        <div class="login-buttons">
                            <button type="submit" class="btn btn-success btn-block btn-md">Submit</button>
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