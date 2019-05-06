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
                        <span class="caption-subject font-dark sbold uppercase">管理员账号列表</span>
                    </div>
                    <div class="actions">
                        @if(in_array("system_account_add",$admin_access))
                            <a href="javascript:;" class="btn green btn-add"> 添加管理员
                                <i class="fa fa-plus"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="sxtj">
                    <div class="row">
                        <div class="col-md-3 col-md-offset-4">
                            <div class="form-horizontal" role="form">
                                <div class="form-group">
                                    <label for="keyword" class="col-sm-3 control-label">
                                        输入查找:
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-filter" name="keyword"
                                               id="keyword" placeholder="管理员姓名、电话、账号" value="" autocomplete="off">
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
                                <th width="15%">省份</th>
                                <th width="20%">管理员姓名</th>
                                <th width="15%">管理员电话</th>
                                <th width="15%">管理员账号</th>
                                <th width="15%">创建时间</th>
                                <th width="15%">操作</th>
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
                            "url": "{{url("system/account")}}" // ajax source
                        },
                        "columnDefs": [{ // define columns sorting options(by default all columns are sortable extept the first checkbox column)
                            'orderable': false,
                            'targets': [0, 3]
                        }],
                        "order": [
                            [0, "asc"]
                        ]// set first column as a default sort by asc
                    }
                });

                //搜索
                $("#btn-search").on("click", function () {
                    $(".form-filter").each(function () {
                        grid.setAjaxParam($(this).attr('name'), $(this).val());
                    });
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

                //删除
                $("#datatable_ajax").on('click', '.btn-del', function () {
                    var id = $(this).attr("data-id");
                    bootbox.confirm('是否确认删除此用户?', function (result) {
                        if (result) {
                            ADMIN.ajax({
                                type: "get",
                                url: "{{url("system/account/delete")}}",
                                data: {"id": id},
                                bootboxtype: "alert",
                                success: function () {
                                    grid.getDataTable().ajax.reload();
                                }
                            });
                        }
                    })
                });

                //添加管理员
                $('.btn-add').on("click", function () {
                    ADMIN.ajax({
                        type: "get",
                        url: "{{url('system/account/add')}}",
                        title: "添加管理员"
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
        });
    </script>
@endsection