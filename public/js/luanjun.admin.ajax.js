/**
 * Created by luanjun on 2017/7/13.
 */
/*
 后台AJAX请求

 必须按照后台标准JSON返回

 __Examples__

 ADMIN.ajax({
     url:"a.php",
     type:"get",//可选择GET或POST,默认GET
     data:{id:1,name:"aaa"},
     title:"管理页面",
     bootboxtype:"dialog",//可选择dialog或者alert
     size:"large",  //可选择large大或者small小，或者为空
     success:function(ret,LJbox){
        alert(111);//执行成功后要执行的函数
     },
     error:function(ret,LJbox){
        alert(222);//执行失败后要执行的函数
     }
 });
 */

jQuery(function ($) {

    if (typeof ADMIN !== "object") {
        ADMIN = {};
    }

    ADMIN.ajax = function (options) {
        if (typeof options !== "object") {
            throw new Error("参数需要是一个对象");
        }

        if (!options.url) {
            throw new Error("请求的URL必须指定");
        }
        if (!options.type) {
            options.type = "post";
        }
        if (!options.data) {
            options.data = "";
        }
        if (!options.title) {
            options.title = "";
        }
        if (!options.bootboxtype) {
            options.bootboxtype = "dialog";
        }
        if (!options.size) {
            options.size = "";
        }
        if (!options.callback) {
            options.callback = function () {

            };
        }
        var box = false;
        jQuery.ajax({
            url: options.url,
            type: options.type,
            dataType: "json",
            async: false,
            data: options.data,
            success: function (ret) {
                if (ret.code === 200) {
                    switch (options.bootboxtype) {
                        case "dialog":
                            box = ADMIN.bootbox({
                                message: ret.message,
                                title: options.title,
                                size: options.size,
                                callback: options.callback
                            });
                            break;
                        case "alert":
                            bootbox.hideAll();

                            box = ADMIN.bootbox({
                                message: ret.message,
                                type: "alert",
                                status: "success",
                                callback: options.callback
                            });

                            setTimeout(function () {
                                bootbox.hideAll();
                            }, 2000);
                            break;
                    }
                } else {
                    if (ret.code == 300) {
                        location.reload(true);
                    } else {
                        box = ADMIN.bootbox({
                            message: ret.message,
                            type: "alert",
                            status: "error",
                            callback: options.callback
                        });
                    }

                }
                if (typeof options.success === "function") {
                    options.success = options.success.call(this, ret, box);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                box = ADMIN.bootbox({
                    message: XMLHttpRequest.status + " " + errorThrown,
                    type: "alert",
                    status: "error"
                });
                var ret = {XMLHttpRequest: XMLHttpRequest, textStatus: textStatus, errorThrown: errorThrown};
                if (typeof options.error === "function") {
                    options.error = options.error.call(this, ret, box);
                }
            }
        });
        return box;
    }
});
