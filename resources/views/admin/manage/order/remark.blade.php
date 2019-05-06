<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-file-text font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"> 备注信息</span>
                </div>
            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" id="remark-form" action="{{url('manage/order/remark')}}" method="post">
                    <input type="hidden" id="id" name="id" value="{{$info->order_id}}">
                    <div class="alert alert-danger display-hide">
                        <button class="close" data-close="alert"></button>
                        <span></span>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>备注信息：
                        </label>
                        <div class="col-md-8">
                            <textarea  class="form-control" name="business_remark" id="business_remark" placeholder="请输入备注信息" maxlength="50" autocomplete="off">{{$info->business_remark}}</textarea>
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
            var theForm = $('#remark-form');

            theForm.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    business_remark: {
                        required: true
                    }
                },

                messages: {
                    business_remark: {
                        required: '请输入备注信息'
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit
                    $('.alert-danger', theForm).find("span").html("请填写必填字段");
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
                                        grid.getDataTable().ajax.reload();
                                        bootbox.hideAll();
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
            bootbox.hideAll();
        });
    });
</script>