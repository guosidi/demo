<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8"/>
    <title>彩票业务管理系统</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="彩票业务管理系统" name="description"/>
    <meta content="" name="author"/>
@section('style')
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet"
              type="text/css"/>
        <link href="{{URL::asset('/assets/global/plugins/font-awesome/css/font-awesome.min.css')}}"
              rel="stylesheet" type="text/css"/>
        <link href="{{URL::asset('/assets/global/plugins/simple-line-icons/simple-line-icons.min.css')}}"
              rel="stylesheet"
              type="text/css"/>
        <link href="{{URL::asset('/assets/global/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet"
              type="text/css"/>
        <link href="{{URL::asset('/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css')}}"
              rel="stylesheet"
              type="text/css"/>
        <!-- END GLOBAL MANDATORY STYLES -->
        <!-- BEGIN THEME GLOBAL STYLES -->
        <link href="{{URL::asset('/assets/global/css/components.min.css')}}" rel="stylesheet"
              id="style_components" type="text/css"/>
        <link href="{{URL::asset('/assets/global/css/plugins.min.css')}}" rel="stylesheet" type="text/css"/>
        <!-- END THEME GLOBAL STYLES -->
        <!-- BEGIN THEME LAYOUT STYLES -->
        <link href="{{URL::asset('/assets/layouts/layout/css/layout.min.css')}}" rel="stylesheet"
              type="text/css"/>
        <link href="{{URL::asset('/assets/layouts/layout/css/themes/darkblue.min.css')}}" rel="stylesheet"
              type="text/css"
              id="style_color"/>
        <link href="{{URL::asset('/assets/layouts/layout/css/custom.min.css')}}" rel="stylesheet"
              type="text/css"/>
        <!-- END THEME LAYOUT STYLES -->
        <link href="{{URL::asset('/css/jquery.toast.min.css')}}" rel="stylesheet"
              type="text/css"/>
    @show
    <link rel="shortcut icon" href="{{URL::asset('favicon.ico')}}"/>
</head>
<!-- END HEAD -->

<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white">
<div class="page-wrapper">
    <!-- BEGIN HEADER -->
@include('layouts.header')
<!-- END HEADER -->
    <!-- BEGIN HEADER & CONTENT DIVIDER -->
    <div class="clearfix"></div>
    <!-- END HEADER & CONTENT DIVIDER -->
    <!-- BEGIN CONTAINER -->
    <div class="page-container">
        <!-- BEGIN SIDEBAR -->
    @include('layouts.navigation')
    <!-- END SIDEBAR -->
        <!-- BEGIN CONTENT -->
        <div class="page-content-wrapper">
            <!-- BEGIN CONTENT BODY -->
            <div class="page-content">
                <!-- BEGIN PAGE HEADER-->

                <!-- BEGIN PAGE BAR -->
            @include('layouts.bar')
            <!-- END PAGE BAR -->

                <!-- END PAGE HEADER-->
                @yield('content')
            </div>
            <!-- END CONTENT BODY -->
        </div>
        <!-- END CONTENT -->
    </div>
    <!-- END CONTAINER -->
    <!-- BEGIN FOOTER -->
@include('layouts.footer')
<!-- END FOOTER -->
</div>
@section('script')
<!--[if lt IE 9]>
<script src="{{URL::asset('/assets/global/plugins/respond.min.js')}}"></script>
<script src="{{URL::asset('/assets/global/plugins/excanvas.min.js')}}"></script>
<script src="{{URL::asset('/assets/global/plugins/ie8.fix.min.js')}}"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="{{URL::asset('/assets/global/plugins/jquery.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/js.cookie.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}"
        type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/jquery.blockui.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}"
        type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="{{URL::asset('/assets/global/scripts/app.min.js')}}" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="{{URL::asset('/assets/layouts/layout/scripts/layout.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/layouts/layout/scripts/demo.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/layouts/global/scripts/quick-sidebar.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/layouts/global/scripts/quick-nav.min.js')}}" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->
<script src="{{URL::asset('/js/jquery.form.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/bootbox/bootbox.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/js/jquery.toast.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/js/luanjun.admin.ajax.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/js/luanjun.admin.bootbox.js')}}" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        bootbox.setDefaults("locale", "zh_CN");

        @if(count($errors)>0)
            $message = '';
        @foreach($errors->all() as $value)
            $message += '<?php echo $value ?>';
        @endforeach
        ADMIN.bootbox({
            message: $message,
            type: "alert",
            status: "error"
        });
        @endif
    });
</script>
@show
</body>

</html>