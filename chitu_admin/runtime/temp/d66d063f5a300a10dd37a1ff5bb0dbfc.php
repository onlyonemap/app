<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:73:"D:\WWW\chitu_admin\public/../application/admin\view\setting\addprice.html";i:1567591959;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="renderer" content="webkit">
<title>赤途后台管理系统</title>
<!--[if lt IE 9]>
<meta http-equiv="refresh" content="0;/static/tpl/ie.html" />
<![endif]-->

<link rel="shortcut icon" href="favicon.ico"> 
<link rel="icon" href="/static/tpl/img/favcion.png" type="image/x-icon" />
<!-- bootstrap -->
<link href="/static/tpl/css/bootstrap.min.css?v=3.3.6" rel="stylesheet" />
<!-- 字体文件 -->
<link href="/static/tpl/css/font-awesome.min.css?v=4.4.0" rel="stylesheet" />
<!-- 动画文件 -->
<!-- <link href="/static/tpl/css/animate.min.css" rel="stylesheet" /> -->

<!-- <link href="/static/tpl/css/plugins/iCheck/custom.css" rel="stylesheet" /> -->

<!-- <link href="/static/tpl/css/plugins/datapicker/datepicker3.css" rel="stylesheet" /> -->

<!-- <link href="/static/tpl/css/plugins/footable/footable.core.css" rel="stylesheet" /> -->
<!--图片放大css-->
<link href="/static/tpl/css/boxImg.css" type="text/css" rel="stylesheet" />
<!-- 页面css -->
<link href="/static/tpl/css/style.min862f.css" rel="stylesheet" />


    <link href="/static/tpl/css/plugins/iCheck/custom.css" rel="stylesheet" />
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">

    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5><a href="javascript:history.back();"><i class="fa fa-chevron-left"></i> 返回 </a></h5>
            <div class="ibox-tools"><span class="" style="color: #337ab7;">设置市内配送参数</span></a></div>
        </div>
        <div class="ibox-content">
            <form class="form-horizontal m-t" id="commentForm" method="post" action="/admin/setting/toaddprice">

                <div class="form-group">
                    <label class="col-sm-2 control-label">分类选择</label>
                    <div class="col-sm-6">
<!--                        <input class="form-control" type="text" name="name" minlength="2"  required="" />-->
                        <select name="cid" id="" class="form-control" >
                            <?php if(is_array($arr) || $arr instanceof \think\Collection || $arr instanceof \think\Paginator): $i = 0; $__LIST__ = $arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo $vo['cid']; ?>"><?php echo $vo['name']; ?></option>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">时间系数<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control"  name="delivery_time"  placeholder="">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">最大门店数<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control"  name="delivery_num"  placeholder="">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">最低标准价格<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control"  name="delivery_low"  placeholder="">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">外环门店配送费<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control" name="delivery_inner"  placeholder="">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">内环门店配送费<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="number" class="form-control"  name="delivery_outer"  placeholder="">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <input type="hidden" name="action" value="add">
                        <button class="btn btn-primary" type="submit">保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>



<!-- 全局js -->
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<!-- bootstrap -->
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
<!-- jQuery Validation plugin javascript-->


<script src="/static/tpl/js/shiftPage.js"></script>
<script language="javascript" src="/static/tpl/ui/form.js"></script>
<link rel="stylesheet" href="/static/tpl/ui/jquery.ui.autocomplete.css">
<script type="text/javascript" src="/static/tpl/ui/jquery.min.js"></script>
<script type="text/javascript" src="/static/tpl/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="/static/tpl/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="/static/tpl/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="/static/tpl/ui/jquery.ui.autocomplete.js"></script>
<!-- jQuery Validation plugin javascript-->
<script src="/static/tpl/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/static/tpl/js/plugins/validate/messages_zh.min.js"></script>
<!-- iCheck -->
<script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>
<!-- 地址三级联动 -->
<script src="/static/tpl/js/common/select-address.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // radio 初始化
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
        // 公司自动补全
        $( "#key" ).autocomplete({
            source: "/admin/common/search",
            minLength: 2,
            autoFocus: true
        });

        // 表单验证
        $("#commentForm").validate({
            errorElement : 'span',
            errorClass : 'help-block',
            rules : {
                ShiftNumber : {
                    required : true,
                    remote : {
                        type:"post",
                        url:"/admin/shift/checknumber",
                        data:{
                            name:function(){
                                return $("#ShiftNumber").val();
                            }
                        }
                    }
                }
            },
            messages : {
                ShiftNumber : {
                    required : "请输入班次号",
                    remote :  "班次号已存在!!!" ,
                },
            },
            //自定义错误消息放到哪里
            errorPlacement : function(error, element) {
                element.next().remove();//删除显示图标
                element.after('<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>');
                element.after(error);//显示错误消息提示
            },
            //给未通过验证的元素进行处理
            highlight : function(element) {
                $(element).closest(".form-group").removeClass("has-success has-feedback").addClass("has-error has-feedback")
            },
            //验证通过的处理
            success : function(label) {
                var el=label.closest('.form-group')
                el.removeClass('has-error has-feedback').addClass("has-success has-feedback");
                el.find('.has-error').remove();
                el.find('.glyphicon-remove').remove();
                var el=label.closest('.form-group').find("input");
                label.remove();
            },
        });
    });
</script>


</body>

</html>
