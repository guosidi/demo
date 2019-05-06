/**
 * Created by luanjun on 2017/7/13.
 */
/*
 后台bootbox弹层请求

 __Examples__

 ADMIN.bootbox({
     url:"a.php",
     type:"dialog",//可选择dialog或者alert 默认dialog
     status:"error",//可选择success或error 默认success
     message:"可以包含HTML",
     title:"标题",//
     size:"large"  //可选择large大或者small小，或者为空
 });
 */


jQuery(function ($) {

    if (typeof ADMIN !== "object") {
        ADMIN = {};
    }

    ADMIN.bootbox = function (options) {
        if (typeof options !== "object") {
            throw new Error("参数需要是一个对象");
        }

        if (!options.type) {
            options.type = "dialog";
        }
        if (!options.status) {
            options.status = "success";
        }
        if (!options.message) {
            options.message = "";
        }
        if (!options.title) {
            options.title = "";
        }
        if (!options.size) {
            options.size = "";
        }
        if (!options.callback) {
            options.callback = function () {

            };
        }
        var box = false;
        switch (options.type) {
            case "dialog":
                box = bootbox.dialog({
                    message: options.message,
                    title: options.title,
                    size: options.size
                });
                break;
            case "alert":
                switch (options.status) {
                    case "success":
                        var successhtml = '<div class="row">' +
                            '<div class="col-md-11">' +
                            '<div class="portlet-body">' +
                            '<div class="alert alert-success">' +
                            '<strong>' + options.message + '</strong>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                        box = bootbox.alert(successhtml, options.callback);
                        break;
                    case "error":
                        var errorhtml = '<div class="row">' +
                            '<div class="col-md-11">' +
                            '<div class="portlet-body">' +
                            '<div class="alert alert-danger">' +
                            '<strong>错误：' + options.message + '</strong>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                        box = bootbox.alert(errorhtml, options.callback);
                        break;
                }
                break;
        }
        return box;
    }
});

