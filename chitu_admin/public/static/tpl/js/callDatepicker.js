/**
 * Created by ly on 2017/3/20.
 * Notes: 日期插件调用
 */
$(function () {
    $(".date-view-two").datepicker({
        language: "zh-CN",
        autoclose: true,//选中之后自动隐藏日期选择框
        clearBtn: true,//清除按钮
        todayBtn: true,//今日按钮
        maxViewMode: 1, // 视图模式的最大限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        minViewMode: 1, // 视图模式的最小限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        format: "yyyy-mm"//日期格式
    });
    $(".date-view-three").datepicker({
        language: "zh-CN",
        autoclose: true,//选中之后自动隐藏日期选择框
        clearBtn: true,//清除按钮
        todayBtn: true,//今日按钮
        maxViewMode: 1, // 视图模式的最大限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        minViewMode: 0, // 视图模式的最小限制 0或“天”或“月”，1或“月”或“年”，2或“年”或“十年”，3或“千年“。
        format: "yyyy-mm-dd"//日期格式
    });
    
    
});
