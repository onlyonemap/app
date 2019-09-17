<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:69:"D:\WWW\chitu_admin\public/../application/admin\view\staff\update.html";i:1564196625;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
                <div class="ibox-tools">平台用户修改</div>
            </div>
            <div class="ibox-content">
                <form class="form-horizontal m-t" method="post" action="/admin/staff/updatemessage" id="commentForm">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">用户名<span style="color:red"> *</span></label>
                        <div class="col-sm-10">
                            <input id="username" value="<?php echo $list['username']; ?>" name="username" minlength="2" type="text" class="form-control" required="" aria-required="true">
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">公司姓名<span style="color:red"> *</span></label>
                        <div class="col-sm-10">
                            <input id="realname" value="<?php echo $list['realname']; ?>" name="realname" minlength="2" type="text" class="form-control" required="" aria-required="true">
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">手机号<span style="color:red"> *</span></label>
                        <div class="col-sm-10">
                            <input id="phone" value="<?php echo $list['tel']; ?>" name="phone" maxlength="11" type="text" class="form-control" required="" aria-required="true">
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">性别</label>
                        <div class="col-sm-10">
                            <label class="radio-inline i-checks">
                                <input type="radio"  <?php if(($list['sex'] == 2)): ?>checked=""<?php endif; ?> value="2" name="sex"> <i></i>女
                            </label>
                            <label class="radio-inline i-checks">
                                <input type="radio" <?php if(($list['sex'] == 1)): ?>checked=""<?php endif; ?> value="1" name="sex"> <i></i>男
                            </label>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">编号<span style="color:red"> *</span></label>
                        <div class="col-sm-10">
                            <input id="number" name="number" value="<?php echo $list['numbers']; ?>" type="text" class="form-control" readonly="readonly" >
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">邮箱<span style="color:red"> *</span></label>
                        <div class="col-sm-10">
                            <input id="email" name="email" value="<?php echo $list['email']; ?>" type="text" class="form-control" >
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">微信号<span style="color:red"> *</span></label>
                        <div class="col-sm-10">
                            <input id="weixin" name="weixin" value="<?php echo $list['weixin']; ?>" type="text" class="form-control" r>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">重置密码</label>
                        <div class="col-sm-10">
                             <input id="password" placeholder="请输入密码" name="password" value="" type="password" class="form-control" >
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                   
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                            <input type="hidden" value="<?php echo $list['aid']; ?>" name="aid" />
                            <button class="btn btn-primary" type="submit">保存</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <!-- iCheck -->
    <script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>
    <!-- 表单验证 -->
    <script src="/static/tpl/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="/static/tpl/js/plugins/validate/messages_zh.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
            // 表单验证
            var e = "<i class='fa fa-times-circle'></i> ";
            $("#commentForm").validate({
                rules: {
                    username: {
                        required: !0,
                        minlength: 2
                    },
                    realname: {
                        required: !0,
                        minlength: 2
                    }
                },
                messages: {
                    username: {
                        required: e + "请输入您的用户名",
                        minlength: e + "用户名必须两个字符以上"
                    },
                    realname: {
                        required: e + "请输入您的真实姓名",
                        minlength: e + "必须2个字符以上"
                    }
                }
            });
        });
    </script>
</body>
</html>
