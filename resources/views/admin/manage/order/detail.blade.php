@extends('layouts.master')

@section('script')
    @parent
    <script src="{{URL::asset('/js/jquery.form.min.js')}}"></script>
    <script src="{{URL::asset('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js')}}"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN GENERAL PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-social-dribbble font-blue-sharp"></i>
                        <span class="caption-subject font-blue-sharp bold uppercase">订单管理/订单明细</span>
                    </div>
                    <div class="actions">
                        <a href="{{url('manage/order')}}" class="btn green btn-xs">返回</a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="bold">订单信息</h4>
                            <p class="muted"> 订单编号：{{$orderInfo->order_id}} </p>
                            <p class="muted"> 订单状态：<span class="bold">{{$orderInfo->order_status}}</span>
                            </p>
                            <p class="muted"> 下单时间：{{$orderInfo->created_at}} </p>
                            <p class="muted"> 支付时间：{{$orderInfo->payed_at}} </p>
                            <p class="muted"> 支付方式：{{$orderInfo->payment_id}} </p>
                            <p class="muted"> 渠道订单号：{{$orderInfo->pay_code}} </p>
                            <p class="muted"> 业务备注：{{$orderInfo->business_remark}} </p>
                        </div>
                        <div class="col-md-6">
                            <h4 class="bold">订单详情</h4>
                            <div class="col-md-12" style="border-bottom:1px solid #000000">
                                @foreach($orderInfo->goodsList as $k=>$v)
                                    <label class="col-md-4 control-label">{{$v->lottery_name}}</label>
                                    <label class="col-md-4 control-label"> x {{$v->goods_number}}</label>
                                    <label class="col-md-4 control-label">&yen;{{$v->goods_price}}</label>
                                @endforeach
                            </div>
                            <label class="col-md-4 control-label col-md-offset-8 bold">
                                合计：&yen;{{$orderInfo->pay_fee}} </label>
                            <label class="col-md-12 control-label bold"> 实付：&yen;{{$orderInfo->pay_fee}} </label>
                            <label class="col-md-12 control-label bold"> 备注：<span
                                        class="text-danger">{{$orderInfo->remark}}</span></label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="bold">收货信息</h4>
                            <p class="muted"> 收货人：{{$orderInfo->consignee}}</p>
                            <p class="muted"> 电话：{{$orderInfo->mobile}}</p>
                            <p class="muted"> 店铺名称：{{$orderInfo->canteen_name}}</p>
                            <p class="muted"> 收货地址：
                                @if(strstr($orderInfo->city_name,$orderInfo->province_name))
                                    {{$orderInfo->city_name}}{{$orderInfo->zone_name}}{{$orderInfo->address}}
                                @else
                                    {{$orderInfo->province_name}}{{$orderInfo->city_name}}{{$orderInfo->zone_name}}{{$orderInfo->address}}
                                @endif
                                ,邮编：@if($orderInfo->zip){{$orderInfo->zip}}@endif</p>
                        </div>
                        <div class="col-md-6">
                            <h4 class="bold">配送信息</h4>
                            <form class="form-horizontal" role="form"
                                  action="{{url('manage/order/handleOrder')}}" method="post"
                                  id="order-form">
                                <div class="alert alert-danger display-hide">
                                    <button class="close" data-close="alert"></button>
                                    <span></span>
                                </div>
                                <input type="hidden" id="order_id" name="order_id"
                                       value="{{$orderInfo->order_id}}">
                                <div class="form-group">
                                    <label class="col-md-4 control-label"><span class="required">*</span>物流公司名称:</label>
                                    <div class="col-md-5">
                                        @if($orderInfo->shipping_code)
                                            <label class="control-label">{{$orderInfo->shipping_name}}</label>
                                        @else
                                            <input type="text" name="shipping_name" id="shipping_name"
                                                   class="form-control input-sm" placeholder="请输入物流公司名称" maxlength="12">
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label">
                                        <span class="required">*</span>快递单号:</label>
                                    <div class="col-md-5">
                                        @if($orderInfo->shipping_code)
                                            <label class="control-label">{{$orderInfo->shipping_code}}</label>
                                        @else
                                            <input type="text" name="shipping_code" id="shipping_code"
                                                   class="form-control input-sm" placeholder="请输入快递单号" maxlength="20">
                                        @endif
                                    </div>
                                </div>
                                @if(!$orderInfo->shipping_code)
                                    <div class="form-actions">
                                        <div class="col-sm-6 pull-right">
                                            <button type="submit"
                                                    class="btn green btn-info btn-sm btn-affirm">发货
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
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
                var theForm = $('#order-form');

                theForm.validate({
                    errorElement: 'span', //default input error message container
                    errorClass: 'help-block', // default input error message class
                    focusInvalid: false, // do not focus the last invalid input
                    rules: {
                        shipping_name: {
                            required: true
                        },
                        shipping_code: {
                            required: true
                        }
                    },

                    messages: {
                        shipping_name: {
                            required: '请输入物流公司名称'
                        },
                        shipping_code: {
                            required: '请输入快递单号'
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
                                            window.location.href = '{{url('manage/order')}}';
                                        }
                                    });
                                } else {
                                    $('.c', theForm).find("span").html(ret.message);
                                    $('.alert-danger', theForm).show();
                                }
                            }
                        });
                    }
                });

            };

            fromValidate();
        });
    </script>
@endsection