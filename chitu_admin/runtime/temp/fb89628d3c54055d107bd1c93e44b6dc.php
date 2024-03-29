<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:71:"D:\WWW\chitu_admin\public/../application/admin\view\index\register.html";i:1568697481;}*/ ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <title> 赤途(上海)供应链管理有限公司</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link href="/static/tpl/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/tpl/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/static/tpl/css/animate.css" rel="stylesheet">
    <link href="/static/tpl/css/style.css" rel="stylesheet">
    <link href="/static/tpl/css/login.css" rel="stylesheet">
    <link rel="icon" href="/static/tpl/img/favcion.png" type="image/x-icon" />
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;/static/tpl/ie.html" />
    <![endif]-->
    <script>
        if (window.top !== window.self) {
            window.top.location = window.location;
        }
    </script>

</head>

<body class="signin">
<div class="signinpanel">
    <div class="row">
        <div class="col-sm-12">
            <form method="post" action="">

                <label class="no-margins" style="font-size: 20px;">注册</label><span class="pull-right" style="font-size: 16px;"><a href="<?php echo url('admin/index/login'); ?>"><span style="color: #ffffff;">登陆</span></a></span>

                <input type="text" class="form-control " name="admin_name" placeholder="请输入用户名" />

                <input type="password" class="form-control  m-b password1" name="user_password"  placeholder="请输入密码" />
                <input type="password" class="form-control  m-b password2" name="confim_password"  placeholder="确认密码" />
                <input type="text" class="form-control " name="realname" placeholder="公司名称">
                <input type="tel" class="form-control " name="phone" placeholder="请输入联系方式">
                <input type="text" class="form-control "name="email" placeholder="请输入邮箱">
                <button id="submit" class="btn btn-success btn-block" onclick="tosubmit()" type="button">注册</button>

            </form>
        </div>
    </div>
    <div class="signup-footer col-sm-12">
        <div class="" style="text-align: center">
            &copy;赤途(上海)供应链管理有限公司
        </div>
    </div>
</div>
</body>
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
<!-- jQuery Validation plugin javascript-->
<script src="/static/tpl/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/static/tpl/js/plugins/validate/messages_zh.min.js"></script>
<!-- iCheck -->
<script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>
<!-- 自动补全 -->
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script type="text/javascript" src="__STATIC__/tpl/js/layer.js"></script>
<script type="text/javascript" src="__STATIC__/tpl/js/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="__STATIC__/tpl/js/h-ui.admin/js/H-ui.admin.js"></script>
<script>
    // $("#submit").ready(function(){
    //      if($('.uname').val()==''){
    //          return false;
    //      }
    // })
    function tosubmit(){
        if($('.uname').val() == ''){
            $('span').empty();
            $('form').append('<span style="color:red;">用户名不能为空！</span>');
            return false;
        }
        if($('.password1').val() == ''){
            $('span').empty();
            $('form').append('<span style="color:red;">密码不能为空！</span>');
            return false;
        }
        if($('.password2').val() == ''){
            $('span').empty();
            $('form').append('<span style="color:red;">确认密码不能为空！</span>');
            return false;
        }
        if($('.password1').val() != $('.password2').val()){
            console.log($('.password1').val());
            console.log($('.password2').val());
            $('span').empty();
            $('form').append('<span style="color:red;">两次密码输入不一致！</span>');
            return false;
        }
        if($('.realname').val() == ''){
            $('span').empty();
            $('form').append('<span style="color:red;">公司名称不能为空！</span>');
            return false;
        }
        if($('.phone').val() == ''){
            $('span').empty();
            $('form').append('<span style="color:red;">联系电话不能为空！</span>');
            return false;
        }
        if($('.email').val() == ''){
            $('span').empty();
            $('form').append('<span style="color:red;">邮箱不能为空！</span>');
            return false;
        }
        $.ajax({
            url:"<?php echo url('admin/index/doregister'); ?>",
            type:'POST',
            data:$('form').serialize(),
            dataType:'json',
            success:function(res){
                if (res.code == '1001'){
                    layer.msg(res.msg, {icon: 2, time: 2000});
                    location.href = '<?php echo url("admin/index/login"); ?>'
                }
            }
        })

    }
</script>

</html>

