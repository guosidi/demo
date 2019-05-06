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
    <title>彩票后台管理系统</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="彩票后台管理系统-抠抠网" name="description"/>
    <meta content="抠抠网" name="author"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet"
          type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/simple-line-icons/simple-line-icons.min.css')}}"
          rel="stylesheet" type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css')}}"
          rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{URL::asset('/assets/global/plugins/select2/css/select2.min.css')}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet"
          type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="{{URL::asset('/assets/global/css/components.min.css')}}" rel="stylesheet" id="style_components"
          type="text/css"/>
    <link href="{{URL::asset('/assets/global/css/plugins.min.css')}}" rel="stylesheet" type="text/css"/>
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="{{URL::asset('/assets/pages/css/login-4.min.css')}}" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <!-- END THEME LAYOUT STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->

<body class="login">
<!-- BEGIN LOGO -->
<div class="logo">
    <h1 class="title">彩票后台管理系统</h1>
</div>
<!-- END LOGO -->
<!-- BEGIN LOGIN -->
<div class="content" style="width: 450px;">
    <!-- BEGIN LOGIN FORM -->
    <form class="login-form" action="{{url("/login")}}" method="post">
        <div class="alert alert-danger display-hide">
            <button class="close" data-close="alert"></button>
            <span>请输入用户名和密码</span>
        </div>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">用户名</label>
            <div class="input-icon">
                <i class="fa fa-user"></i>
                <input class="form-control placeholder-no-fix" type="text" maxlength="20" autocomplete="off" placeholder="请输入用户名"
                       id="account" name="account"/></div>
        </div>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">密码</label>
            <div class="input-icon">
                <i class="fa fa-lock"></i>
                <input class="form-control placeholder-no-fix" type="password" maxlength="20" autocomplete="off" placeholder="请输入密码"
                       id="password" name="password"/></div>
        </div>
        <div class="form-group">
            <label class="rememberme mt-checkbox mt-checkbox-outline">
                如账号存在登录问题，请拨打咨询电话400-8859-000
            </label>
            <button type="submit" class="btn btn-block green"> 登录</button>
        </div>
    </form>
    <!-- END LOGIN FORM -->
</div>
<!-- END LOGIN -->
<!-- BEGIN COPYRIGHT -->
<div class="copyright"> 2018 &copy; 抠抠网</div>
<!-- END COPYRIGHT -->
<!--[if lt IE 9]>
<script src="{{URL::asset('/assets/global/plugins/respond.min.js')}}"></script>
<script src="{{URL::asset('/assets/global/plugins/excanvas.min.js')}}"></script>
<script src="{{URL::asset('/assets/global/plugins/ie8.fix.min.js')}}"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="{{URL::asset('/assets/global/plugins/jquery.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/bootstrap/js/bootstrap.min.js')}}"
        type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/js.cookie.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}"
        type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/jquery.blockui.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}"
        type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="{{URL::asset('/js/jquery.form.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js')}}"
        type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/jquery-validation/js/additional-methods.min.js')}}"
        type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/select2/js/select2.full.min.js')}}"
        type="text/javascript"></script>
<script src="{{URL::asset('/assets/global/plugins/backstretch/jquery.backstretch.min.js')}}"
        type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="{{URL::asset('/assets/global/scripts/app.min.js')}}" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<script src="{{URL::asset('/assets/global/plugins/bootbox/bootbox.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/js/luanjun.admin.ajax.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('/js/luanjun.admin.bootbox.js')}}" type="text/javascript"></script>

<script type="text/javascript">
    jQuery(document).ready(function () {

        var loginValidate = function () {
            var theForm = $('.login-form');

            theForm.validate({
                errorElement: 'span',
                errorClass: 'help-block',
                focusInvalid: false,
                rules: {
                    account: {
                        required: true,
                        maxlength:20
                    },
                    password: {
                        required: true,
                        maxlength:20
                    },
                    remember: {
                        required: false
                    }
                },

                messages: {
                    account: {
                        required: "请输入正确的用户名"
                    },
                    password: {
                        required: "请输入密码"
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit
                    $('.alert-danger', theForm).find("span").html("请输入用户名和密码");
                    $('.alert-danger', theForm).show();
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },

                success: function (label) {
                    label.closest('.form-group').removeClass('has-error');
                    label.remove();
                },

                errorPlacement: function (error, element) {
                    error.insertAfter(element.closest('.input-icon'));
                },

                submitHandler: function (form) {
                    $(form).ajaxSubmit({
                        type: "post",
                        data: theForm.serialize(),
                        success: function (ret) {
                            if (ret.code == 200) {
                                window.location.href = "/";
                            } else {
                                $('.alert-danger', theForm).find("span").html(ret.message);
                                $('.alert-danger', theForm).show();
                                $('#password').val("");
                            }
                        }
                    });
                }
            });

            $('.login-form input').keypress(function (e) {
                var ev = document.all ? window.event : e;
                if (ev.keyCode == 13) {
                    theForm.submit();
                }
            });

            $.backstretch([
                    "/assets/pages/media/bg/1.jpg",
                    "/assets/pages/media/bg/2.jpg",
                    "/assets/pages/media/bg/3.jpg",
                    "/assets/pages/media/bg/4.jpg"
                ], {
                    fade: 1000,
                    duration: 8000
                }
            );
        };

        loginValidate();
    });
</script>
</body>

</html>