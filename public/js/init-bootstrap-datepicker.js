/**
 * Created by luanjun on 2017/7/17.
 */

/**
 * 初始化日历控件
 * 只要带有.date-picke样式，均会被渲染
 */
$(function () {
    $('.date-picker').datepicker({
        rtl: App.isRTL(),
        autoclose: true,
        language: 'zh-CN'
        //pickerPosition: "bottom-left"
    });
});