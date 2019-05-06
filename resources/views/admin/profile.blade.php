@extends('layouts.master')

@section('script')
    @parent
    <script src="{{URL::asset('/js/jquery.form.min.js')}}"></script>
    <script src="{{URL::asset('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js')}}"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-file-text font-green-sharp"></i>
                        <span class="caption-subject font-green-sharp bold uppercase"> 账户设置</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    <form class="form-horizontal" id="account-form" action="{{url('/profile')}}" method="post">
                        <div class="alert alert-danger display-hide">
                            <button class="close" data-close="alert"></button>
                            <span></span>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">
                                <span class="required">*</span>账号：
                            </label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="account" id="account"
                                       placeholder="请输入管理员账号" value="{{$admin->account}}"
                                       maxlength="20" autocomplete="off" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">
                                <span class="required">*</span>原密码：
                            </label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="password" class="form-control" name="old" id="old" placeholder="请输入原密码"
                                           minlength="6" maxlength="20" autocomplete="off">
                                    <a href="javascript:;" class="input-group-addon clear_input" data-action="old">
                                        <i class="fa fa-close font-red"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">
                                <span class="required">*</span>新密码：
                            </label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="password" class="form-control" name="new" id="new" placeholder="请输入新密码"
                                           minlength="6" maxlength="20" autocomplete="off">
                                    <a href="javascript:;" class="input-group-addon clear_input" data-action="new">
                                        <i class="fa fa-close font-red"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">
                                <span class="required">*</span>确认新密码：
                            </label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="password" class="form-control" name="repwd" id="repwd"
                                           placeholder="请输入确认新密码"
                                           minlength="6" maxlength="20" autocomplete="off">
                                    <a href="javascript:;" class="input-group-addon clear_input" data-action="repwd">
                                        <i class="fa fa-close font-red"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-info btn-sm btn-affirm pull-right">
                                    <i class="fa fa-save"></i> 确认
                                </button>
                            </div>
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-danger btn-sm btn-close pull-left">
                                    <i class="fa fa-close"></i> 关闭
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    @parent
    <script type="text/javascript">
        $(function () {

            var fromValidate = function () {
                var theForm = $('#account-form');

                jQuery.validator.addMethod("verify", function (value, element) {
                    var flag = 1;
                    $.ajax({
                        type: 'POST',
                        url: '{{url('/verify')}}',
                        async: false,
                        data: {password: value},
                        success: function (data) {
                            if (data.code === 200) {
                                flag = 0;
                            }
                        }
                    });

                    if (flag === 0) {
                        return true;
                    } else {
                        return false;
                    }
                }, "原密码输入错误");

                theForm.validate({
                    errorElement: 'span', //default input error message container
                    errorClass: 'help-block', // default input error message class
                    focusInvalid: false, // do not focus the last invalid input
                    rules: {
                        old: {
                            required: true,
                            minlength: 6,
                            maxlength: 20,
                            verify: true
                        },
                        new: {
                            required: true,
                            minlength: 6,
                            maxlength: 20
                        },
                        repwd: {
                            required: true,
                            minlength: 6,
                            maxlength: 20,
                            equalTo: '#new'
                        }
                    },

                    messages: {
                        old: {
                            required: '请输入原密码',
                            minlength: '至少输入6位',
                            maxlength: '最多输入20位'
                        },
                        new: {
                            required: '请输入新密码',
                            minlength: '至少输入6位',
                            maxlength: '最多输入20位'
                        },
                        repwd: {
                            required: '请输入确认新密码',
                            minlength: '至少输入6位',
                            maxlength: '最多输入20位',
                            equalTo: '两次输入的密码不同'
                        }
                    },

                    invalidHandler: function (event, validator) { //display error alert on form submit
                        $('.alert-danger', theForm).find("span").html("请查看错误提示");
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
                        if (element.parent(".input-group").size() > 0) {
                            error.insertAfter(element.parent(".input-group"));
                        } else if (element.attr("data-error-container")) {
                            error.appendTo(element.attr("data-error-container"));
                        } else if (element.parents('.radio-list').size() > 0) {
                            error.appendTo(element.parents('.radio-list').attr("data-error-container"));
                        } else if (element.parents('.radio-inline').size() > 0) {
                            error.appendTo(element.parents('.radio-inline').attr("data-error-container"));
                        } else if (element.parents('.checkbox-list').size() > 0) {
                            error.appendTo(element.parents('.checkbox-list').attr("data-error-container"));
                        } else if (element.parents('.checkbox-inline').size() > 0) {
                            error.appendTo(element.parents('.checkbox-inline').attr("data-error-container"));
                        } else {
                            error.insertAfter(element); // for other inputs, just perform default behavior
                        }
                    },

                    submitHandler: function (form) {
                        $(form).ajaxSubmit({
                            type: "post",
                            data: theForm.serialize(),
                            success: function (ret) {
                                if (ret.code == 200) {
                                    ADMIN.bootbox({
                                        type: "alert",
                                        status: "success",
                                        message: ret.message,
                                        title: ret.title,
                                        callback: function () {
                                            bootbox.hideAll();
                                            window.location.href = '/';
                                        }
                                    });
                                } else {
                                    $('.alert-danger', theForm).find("span").html(ret.message);
                                    $('.alert-danger', theForm).show();
                                }
                            }
                        });
                    }
                });

            };

            fromValidate();

            $(".btn-close").on("click", function () {
                window.location.href = '/';
            });

            $('.clear_input').on('click', function () {
                var ele = $(this).attr('data-action');
                $('#' + ele).val('');
            });
        });
    </script>
@endsection