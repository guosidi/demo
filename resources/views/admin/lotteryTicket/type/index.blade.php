@extends('layouts.master')

@section('style')
    @parent
    <link href="{{URL::asset('/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}"
          rel="stylesheet" type="text/css"/>
    <link href="{{URL::asset('/assets/global/plugins/bootstrap-select/css/bootstrap-select.css')}}"
          rel="stylesheet" type="text/css"/>
    <link href="{{URL::asset('/css/sxtj.css')}}" rel="stylesheet" type="text/css"/>
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
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Begin: Demo Datatable 1 -->
            <div class="portlet light portlet-fit portlet-datatable bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-list"></i>
                        <span class="caption-subject font-dark sbold uppercase">彩票类型管理</span>
                    </div>
                    <div class="actions">
                        @if(in_array("lotteryTicket_type_add",$admin_access))
                            <a href="javascript:;" class="btn green btn-add"> 添加彩票
                                <i class="fa fa-plus"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="sxtj">
                    <div class="row">
                        <div class="col-md-2 col-md-offset-3">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <select id="province_id" class="bs-select form-control input-inline form-filter"
                                                name="province_id">
                                            <option value="">请选择省份</option>
                                            @foreach($provinceList as $v)
                                                <option value="{{ $v->province_id }}">{{ $v->province_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <select id="status" class="bs-select form-control input-inline form-filter"
                                                name="status">
                                            <option value="">状态</option>
                                            <option value="1">在售中</option>
                                            <option value="0">已停售</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <label for="keyword" class="col-sm-2 control-label">
                                        输入查找:
                                    </label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-filter" name="keyword"
                                               id="keyword" placeholder="彩种名称" value="" autocomplete="off">
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
                                <th width="5%">id&nbsp;#</th>
                                <th width="10%">省份</th>
                                <th width="10%">图片</th>
                                <th width="15%">彩种名称</th>
                                <th width="20%">彩票简述</th>
                                <th width="5%">供货价</th>
                                <th width="5%">库存</th>
                                <th width="10%">编辑时间</th>
                                <th width="10%">状态</th>
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
                    },
                    onError: function (grid) {
                        // execute some code on network or other general error
                    },
                    onDataLoad: function (grid) {
                        // execute some code on ajax data load
                        $(".form-filter").each(function () {
                            console.log(this);
                            grid.setAjaxParam($(this).attr('name'), $(this).val());
                        });
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
                            "url": "{{url("lotteryTicket/type")}}" // ajax source
                        },
                        "columnDefs": [{ // define columns sorting options(by default all columns are sortable extept the first checkbox column)
                            'orderable': false,
                            'targets': [2, 9]
                        }],
                        "order": [
                            [1, "asc"]
                        ]// set first column as a default sort by asc
                    }
                });

                //搜索
                $("#btn-search").on("click", function () {
                    $(".form-filter").each(function () {
                        console.log(this);
                        grid.setAjaxParam($(this).attr('name'), $(this).val());
                    });
                    grid.getDataTable().ajax.reload();
                    grid.clearAjaxParams();
                });

                //重置
                $("#btn-reset").on("click", function () {
                    $(".sxtj").find(":text").val("");
                    $(".bs-select").each(function () {
                        $(this).get(0).selectedIndex = 0;//回到初始状态
                        $(this).selectpicker('refresh');//下拉框进行重置刷新
                    });
                    $("#btn-search").trigger("click");
                });

                //删除
                $("#datatable_ajax").on('click', '.btn-del', function () {
                    var id = $(this).attr("data-id");
                    bootbox.confirm('是否确认删除此彩票?', function (result) {
                        if (result) {
                            ADMIN.ajax({
                                type: "get",
                                url: "{{url('lotteryTicket/type/delete')}}",
                                data: {"id": id},
                                bootboxtype: "alert",
                                success: function () {
                                    grid.getDataTable().ajax.reload();
                                }
                            });
                        }
                    })
                });

                //添加彩票
                $('.btn-add').on("click", function () {
                    ADMIN.ajax({
                        type: "get",
                        url: "{{url('lotteryTicket/type/add')}}",
                        title: "添加彩票"
                    });
                });
                //编辑彩票
                $('#datatable_ajax').on("click", '.btn-edit', function () {
                    ADMIN.ajax({
                        type: "get",
                        url: "{{url('lotteryTicket/type/edit')}}",
                        title: "编辑彩票",
                        data: {
                            id: $(this).data('id')
                        }
                    });
                });
                //重置密码
                $('#datatable_ajax').on('click', '.btn-reset', function () {
                    var id = $(this).attr('data-id');
                    bootbox.confirm('是否确认重置此用户的密码?', function (result) {
                        if (result) {
                            ADMIN.ajax({
                                type: "get",
                                url: "{{url("system/account/reset")}}",
                                data: {"id": id},
                                bootboxtype: "alert",
                                success: function () {
                                    grid.getDataTable().ajax.reload();
                                }
                            });
                        }
                    })
                });

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
            if ($("#province_id option").size() == 2) {
                $("#province_id option:first").remove();
                $('#province_id').selectpicker('refresh');
            }
        });
    </script>
@endsection