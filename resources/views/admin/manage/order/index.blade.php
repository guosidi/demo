@extends('layouts.master')

@section('style')
    @parent
    <link href="{{URL::asset('/css/sxtj.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}"
          rel="stylesheet" type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}"
          rel="stylesheet" type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}"
          rel="stylesheet" type="text/css"/>
@endsection

@section('script')
    @parent
    <script src="{{URL::asset('/assets/global/scripts/datatable.js')}}"></script>
    <script src="{{URL::asset('/assets/global/plugins/datatables/datatables.min.js')}}"></script>
    <script src="{{URL::asset('/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}}"></script>
    <script src="{{URL::asset('/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js')}}"></script>
    <script src="{{URL::asset('/assets/pages/scripts/components-bootstrap-select.min.js')}}"></script>
    <script src="{{URL::asset('/js/jquery.form.min.js')}}"></script>
    <script src="{{URL::asset('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js')}}"></script>
    <script src="{{URL::asset('/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"
            type="text/javascript"></script>
    <script src="{{URL::asset('/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js')}}"
            type="text/javascript"></script>
    <script src="{{URL::asset('/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}}"
            type="text/javascript"></script>
    <script src="{{URL::asset('/assets/global/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js')}}"
            type="text/javascript"></script>
    <script src="{{URL::asset('/js/init-bootstrap-datepicker.js')}}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Begin: Demo Datatable 1 -->
            <div class="portlet light portlet-fit portlet-datatable bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-list"></i>
                        <span class="caption-subject font-dark sbold uppercase">订单管理列表</span>
                    </div>
                </div>

                <div class="sxtj">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">
                                        订单状态：
                                    </label>
                                    <div class="col-md-5">
                                        <div class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-default btn-status active">
                                                <input type="radio" name="status" class="toggle" value=""> 全部
                                            </label>
                                            @foreach($orderStatus as $k=>$v)
                                                <label class="btn btn-default btn-status" data-status="{{$k}}">
                                                    <input type="radio" name="status" class="toggle"
                                                           value="{{$k}}"> {{$v}}
                                                    @if($k==1)
                                                        <span class="badge badge-danger order-count"> 0 </span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <div class="col-md-6">
                                        <select class="form-control form-filter" id="province_id"
                                                name="province_id"
                                                data-autocommit="true">
                                            @if(!$auth_province_id)
                                                <option value="">选择省份</option>
                                            @endif
                                            @foreach($openProvinceList as $v)
                                                <option value="{{$v->province_id}}">{{$v->province_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-control form-filter" id="city_id" name="city_id"
                                                data-autocommit="true">
                                            <option value="">选择城市</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">
                                        下单日期：
                                    </label>
                                    <div class="col-sm-9">
                                        <div class="input-group date date-picker col-sm-5 pull-left"
                                             data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control form-filter" id="start_time"
                                                   name="start_time" readonly placeholder="起始日期">
                                            <span class="input-group-btn"><button class="btn default" type="button"><i
                                                            class="fa fa-calendar"></i></button></span>
                                        </div>
                                        <label class="col-sm-2 pull-left data_text">至</label>
                                        <div class="input-group date date-picker col-sm-5 pull-left"
                                             data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control form-filter" id="end_time"
                                                   name="end_time" readonly placeholder="结束日期">
                                            <span class="input-group-btn"><button class="btn default" type="button"><i
                                                            class="fa fa-calendar"></i></button></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <label for="keyword" class="col-sm-3 control-label">
                                        条件查询:
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-filter" name="keywords"
                                               id="keywords"
                                               placeholder="订单号、商家名称、收货电话">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row margin-bottom-20">
                        <div class="col-md-12">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <a type="button" class="btn btn-primary pull-right" id="btn-search">
                                            <i class="fa fa-search"></i>&nbsp;筛选
                                        </a>
                                    </div>
                                    <div class="col-sm-6">
                                        <a type="button" class="btn btn-default" id="btn-reset">
                                            <i class="fa fa-mail-reply"></i> 重置
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover table-checkable"
                               id="datatable_ajax">
                            <thead>
                            <tr role="row" class="heading">
                                <th width="10%">订单ID</th>
                                <th width="10%">商家信息</th>
                                <th width="10%">收货人</th>
                                <th width="20%">收货地址</th>
                                <th width="10%">收货电话</th>
                                <th width="5%">订单金额</th>
                                <th width="5%">订单状态</th>
                                <th width="10%">下单时间</th>
                                <th width="10%">支付时间</th>
                                <th width="10%">操作</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>


                </div>
            </div>
            <!-- End: Demo Datatable 1 -->
        </div>
    </div>
@endsection

@section('script')
    @parent
    <script type="text/javascript">
        var grid;
        var TableDatatablesAjax = function () {

            var handle = function () {
                grid = new Datatable();
                grid.init({
                    src: $("#datatable_ajax"),
                    onSuccess: function (grid, response) {
                        // grid:        grid object
                        // response:    json object of server side ajax response
                        // execute some code after table records loaded
                        if (typeof(response.order_count) == "undefined") {
                            $('.order-count').html(0)
                        } else {
                            $('.order-count').html(response.order_count)
                        }
                    },

                    onError: function (grid) {
                        // execute some code on network or other general error
                    },
                    onDataLoad: function (grid) {
                        // execute some code on ajax data load
                        $(".form-filter").each(function () {
                            grid.setAjaxParam($(this).attr('name'), $(this).val());
                        });

                        grid.setAjaxParam('status', $('input[name="status"]:checked').val());
                    },
                    dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

                        // save datatable state(pagination, sort, etc) in cookie.
                        "bStateSave": false,

                        "lengthMenu": [
                            [10, 20, 50, 100, 150],
                            [10, 20, 50, 100, 150] // change per page values here
                        ],
                        "pageLength": 20, // default record count per page
                        "ajax": {
                            "url": "{{url("manage/order")}}" // ajax source
                        },
                        "columnDefs": [{ // define columns sorting options(by default all columns are sortable extept the first checkbox column)
                            'orderable': false,
                            'targets': [3, 9]
                        }],
                        "order": [
                            [7, "desc"]
                        ]// set first column as a default sort by asc
                    }
                });

                //状态
                $('.btn-status').on('click', function () {
                    var status = $(this).attr('data-status');
                    $(".form-filter").each(function () {
                        grid.setAjaxParam($(this).attr('name'), $(this).val());
                    });
                    grid.setAjaxParam('status', status);
                    grid.getDataTable().ajax.reload();
                    grid.clearAjaxParams();
                })

                //搜索
                $("#btn-search").on("click", function () {
                    $(".form-filter").each(function () {
                        grid.setAjaxParam($(this).attr('name'), $(this).val());
                    });

                    grid.setAjaxParam('status', $('input[name="status"]:checked').val());
                    grid.getDataTable().ajax.reload();
                    grid.clearAjaxParams();
                });

                //重置
                $("#btn-reset").on("click", function () {
                    $(".sxtj").find(":text").val("");
                    $(".sxtj").find("select").each(function () {
                        $(this).get(0).selectedIndex = 0;//回到初始状态
                        $(this).trigger("change");
                    });
                    $("#btn-search").trigger("click");
                });
                //明细、发货
                $('#datatable_ajax').on('click', '.btn-detail', function () {
                    var id = $(this).attr('data-id');
                    window.location.href = '{{url('manage/order/detail')}}/' + id;
                });
                //备注
                $('#datatable_ajax').on('click', '.btn-remark', function () {
                    var orderId = $(this).attr('data-id');
                    ADMIN.ajax({
                        type: "get",
                        url: "{{url('manage/order/remark')}}",
                        data: {id: orderId},
                        title: "添加备注"
                    });
                });

                $('#province_id').on('change', function () {
                    var id = $(this).val();
                    if (!id) {
                        $('#city_id').empty();
                        $('#city_id').append("<option value=\"\">选择城市</option>");
                    } else {
                        $.ajax({
                            type: "post",
                            url: "{{url('common/area')}}",
                            data: {id: id},
                            success: function (ret) {
                                if (ret.code == 200) {
                                    $('#city_id').empty();
                                    $('#city_id').append("<option value=\"\">选择城市</option>");
                                    $.each(ret.data, function (i, v) {
                                        $('#city_id').append("<option value='" + v.id + "'>" + v.name + "</option>");
                                    });
                                }
                            }
                        });
                    }
                });
                @if($auth_province_id)
                $('#province_id').change();
                @endif

            };

            return {
                //main function to initiate the module
                init: function () {
                    handle();
                }
            };

        }();

        $(function () {
            TableDatatablesAjax.init();
        });
    </script>
@endsection