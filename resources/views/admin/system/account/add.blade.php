<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-file-text font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"> 添加管理员</span>
                </div>
            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" id="account-form" action="{{url('system/account/add')}}" method="post">
                    <div class="alert alert-danger display-hide">
                        <button class="close" data-close="alert"></button>
                        <span></span>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>省份：
                        </label>
                        <div class="col-md-8">
                            <select class="bs-select form-control form-filter" id="province_id" name="province_id"
                                    data-autocommit="true">
                                <option value="">请选择</option>
                                @foreach($province as $v)
                                    <option value="{{$v->province_id}}">{{$v->province_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>姓名：
                        </label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="real_name" id="real_name"
                                   placeholder="请输入管理员姓名"
                                   maxlength="10" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>电话：
                        </label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="phone" id="phone" placeholder="请输入管理员电话"
                                   maxlength="11" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>账号：
                        </label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="account" id="account" placeholder="请输入管理员账号"
                                   maxlength="20" autocomplete="off">
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
<script type="text/javascript">
    $(function () {

        var fromValidate = function () {
            var theForm = $('#account-form');

            jQuery.validator.addMethod("isMobile", function (value, element) {
                var length = value.length;
                return this.optional(element) || (length == 11 && /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/.test(value));
            }, "请输入正确格式的手机号码");

            jQuery.validator.addMethod("verify_account", function (value, element) {
                let flag = 1;
                $.ajax({
                    type: "POST",
                    url: "{{url('common/verify')}}",
                    async: false,
                    data: {data: value, type: "system-account"},
                    success: function (data) {
                        if (data.code === 200) {
                            flag = 1;
                        } else {
                            flag = 0
                        }
                    }
                });
                if (!flag) {
                    return false;
                } else {
                    return true;
                }
            }, "账号已存在");

            theForm.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                onfocusout: function (element) {
                    $(element).valid();
                },
                rules: {
                    province_id: {
                        required: true
                    },
                    real_name: {
                        required: true
                    },
                    phone: {
                        required: true,
                        isMobile: true
                    },
                    account: {
                        required: true,
                        verify_account: true
                    }
                },

                messages: {
                    province_id: {
                        required: '请选择省份'
                    },
                    real_name: {
                        required: '请输入管理员姓名'
                    },
                    phone: {
                        required: '请输入正确格式的手机号'
                    },
                    account: {
                        required: '请输入管理员账号'
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit
                    $('.alert-danger', theForm).find("span").html("请查看错误详情");
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
                    $(form).find(":submit").attr("disabled", true);
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
                                        grid.getDataTable().ajax.reload();
                                        bootbox.hideAll();
                                    }
                                });
                            } else {
                                $(form).find(":submit").attr("disabled", false);
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
            bootbox.hideAll();
        });
    });
</script>