<link rel="stylesheet" href="{{ URL::asset('/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css') }}">
<script src="{{ URL::asset('/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js') }}"></script>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-file-text font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"> 编辑彩票</span>
                </div>
            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" id="account-form" target="_blank" action="{{url('lotteryTicket/type/edit')}}" method="post">
                    <div class="alert alert-danger display-hide">
                        <button class="close" data-close="alert"></button>
                        <span></span>
                    </div>
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="form-group">
                        <label class="control-label col-md-3"><span class="required">*</span>商品图上传：</label>
                        <div class="col-md-9">
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                    <img src="{{ $data->pic }}" alt="" width="100%"> </div>
                                <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"> </div>
                                <div>
                                    <span class="btn default btn-file">
                                        <span class="fileinput-new">选择图片</span>
                                        <span class="fileinput-exists">修改图片</span>
                                        <input id="pic" type="file" name="pic" accept="image/gif,image/png,image/jpg">
                                    </span>
                                    <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput">移除</a>
                                </div>
                            </div>
                            <div class="clearfix margin-top-10">
<!--                                <span class="label label-danger">NOTE!</span>-->
                                只支持Jpg、Png、Gif，大小不超过2M
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>省份：
                        </label>
                        <div class="col-md-8">
                            <select class="bs-select form-control form-filter" id="province_id_pop" name="province_id"
                                    data-autocommit="true" data-rule-required="true" data-msg="请选择省份">
                                <option value="">请选择</option>
                                @foreach($provinceList as $v)
                                    <option value="{{$v->province_id}}">{{$v->province_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>彩种名称：
                        </label>
                        <div class="col-md-8">
                            <input type="text" value="{{ $data->name }}" class="form-control" name="name" id="name" placeholder="请输入彩种名称"
                                   maxlength="6" autocomplete="off" data-rule-required="true" data-msg="请输入彩种名称">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>彩种简述：
                        </label>
                        <div class="col-md-8">
                            <textarea name="summary" id="summary" class="form-control" data-rule-required="true" data-msg="最多输入25个描述文字" placeholder="最多输入25个描述文字" maxlength="25">{{ $data->summary }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>供货价：
                        </label>
                        <div class="col-md-8">
                            <div style="position: absolute;right: 45px;top: 7px;color: grey;">元/包</div>
                            <input type="text" value="{{ $data->price }}" class="form-control" name="price" id="price" placeholder="请输入供货价，如：300.00                              元/包"
                                   maxlength="10" min="1" max="9999999999" autocomplete="off" data-rule-required="true" data-msg="请输入供货价">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            <span class="required">*</span>库存：
                        </label>
                        <div class="col-md-8">
                            <input type="text" value="{{ $data->remain }}" class="form-control" name="remain" id="remain" placeholder="输入库存数量，从1到9999的正整数"
                                   min="1" max="9999" autocomplete="off" data-rule-required="true" data-msg="输入库存数量">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">状态开关：</label>
                        <div class="col-md-9">
                            <div class="mt-radio-inline">
                                <label class="mt-radio">
                                    <input type="radio" name="status" id="status1" value="1" {{ $data->status==1?'checked':'' }}> 上架
                                    <span></span>
                                </label>
                                <label class="mt-radio">
                                    <input type="radio" name="status" id="status0" value="0" {{ $data->status==0?'checked':'' }}> 停售
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">上次编辑时间：</label>
                        <div class="col-md-9">
                            <div class="mt-radio-inline">
                                {{ $data->updated_at }}
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
                            <button type="button" class="btn btn-danger btn-sm btn-close">
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
    $.fn.serializeObject = function() {  
        var o = {};  
        var a = this.serializeArray();  
        $.each(a, function() {  
            if (o[this.name]) {  
                if (!o[this.name].push) {  
                    o[this.name] = [ o[this.name] ];  
                }  
                o[this.name].push(this.value || '');  
            } else {  
                o[this.name] = this.value || '';  
            }  
        });  
        return o;  
    }
    $(function () {
        $(".thumbnail").on("click","img",function(){
            var message = "<br>"+"<img src='"+$(this).prop("src")+"' width=100%>";
            bootbox.dialog({
                message:message
            });
            $(".modal-dialog").eq(1).css("width","80%");
        });
        var fromValidate = function () {
            var theForm = $('#account-form');

            jQuery.validator.addMethod("isMobile", function (value, element) {
                var length = value.length;
                return this.optional(element) || (length == 11 && /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/.test(value));
            }, "请正确填写您的手机号码");
            jQuery.validator.addMethod("imgSize", function (value, element) {
                if(element.files[0]){
                    var maxSize = 2048000,
                        picSize = element.files[0].size,
                        type = /image\/(gif|jpg|jpeg|png|GIF|JPG|PNG)$/.test(element.files[0].type);
                    return (maxSize>picSize&&type);
                }else{
                    return true;
                }
            }, "只支持Jpg、Png、Gif，大小不超过2M");
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
                    summary:{
                        maxlength:25
                    },
                    name:{
                        maxlength: 6,
                        required: true
                    },
                    remain:{
                        min: 1,
                        max: 9999,
                        required: true
                    },
                    price:{
                        //digits:true,
                        maxlength:10,
                        min: 1,
                        max: 9999999999
                    },
                    pic:{
                        imgSize:true,
                    }
                },

                messages: {
                    province_id: {
                        required: '请选择省份'
                    },
                    name:{
                        required:'请输入彩种名称'
                    },
                    remain:{
                        min:'请输入合适的库存量',
                        max:'请输入合适的库存量'
                    },
                    price:{
                        maxlength:"请输入正确的价格",
                        min:"请输入正确的价格",
                        max:"请输入正确的价格",
                        //digits:"请输入整数"
                    },
                    pic:{
                        imgSize:"只支持Jpg、Png、Gif，大小不超过2M"
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
                    var formData = new FormData();
                    $.each(theForm.serializeObject(),function(i,n){
                        formData.append(i,n);
                    });

                    var file = document.getElementById("pic");
                    if(file.files[0]){
                        var fileObj = file.files[0];
                        formData.append("file", fileObj);
                    }

                    $.ajax({
                        type: "post",
                        url: theForm.attr("action"),
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
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
        $('#province_id_pop').val("{{ $data->province_id }}").change();

        if($("#province_id_pop option").size()==2){
            $("#province_id_pop option:first").remove();
            $('#province_id_pop').selectpicker('refresh'); 
        }
        
    });
</script>
