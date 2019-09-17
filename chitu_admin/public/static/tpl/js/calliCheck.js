/**
 * Created by ly on 2017/3/23.
 * Notes: iCheck 选择框插件
 */
$(function() {
    $(".i-checks").iCheck({
        // 复选框样式类
        checkboxClass:"icheckbox_square-green",

        // 单选按钮样式类
        radioClass:"iradio_square-green" ,

        // 复选框选中样式类
        // checkedCheckboxClass: "",

        // 单选按钮选中样式类
        // checkedRadioClass: "",

        // 复选框非选中样式类
        // uncheckedCheckboxClass: "unchecked",

        // 单选按钮非选中样式类
        // uncheckedRadioClass: "unchecked",

        // 复选框disabled样式类
        // disabledCheckboxClass: disabled'',

        // 单选按钮disabled样式类
        // disabledRadioClass: 'disabled',

        // 鼠标移动样式
        // hoverClass: 'hover',

        // 聚焦样式
        // focusClass: 'focus',

        // 移动样式
        //activeClass: 'active',

        // 设置label悬停
        //labelHover: true,

        // 设置label悬停样式
        //labelHoverClass: 'hover',

        // increase clickable area by given % (negative number to decrease)
        //increaseArea: '',

        // 小手
        //cursor: false,

        // set true to inherit original input's class name
        //inheritClass: false,

        // if set to true, input's id is prefixed with 'iCheck-' and attached
        //inheritID: false,

        // add HTML code or text inside customized input
        //insert: ''
    });
});