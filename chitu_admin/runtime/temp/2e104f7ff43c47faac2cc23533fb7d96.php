<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:68:"D:\WWW\chitu_admin\public/../application/admin\view\index\login.html";i:1564021633;}*/ ?>
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
               <form method="post" action="/admin/index/login_send" id="form">
                    <label class="no-margins" style="font-size: 20px">登录</label><span class="pull-right" style="font-size: 16px;"><a href="<?php echo url('admin/index/register'); ?>"><span style="color: #ffffff;">立即注册</span></a></span>
                    <input type="text" class="form-control uname" name="admin_name" placeholder="请输入用户名" />
                    <input type="password" class="form-control pword m-b" name="admin_password"  placeholder="请输入密码" />
                    <button class="btn btn-success btn-block"  type="submit">登录</button>

               </form>

            </div>
        </div>
        <div class="signup-footer">
            <div class="pull-left">
                &copy;赤途(上海)供应链管理有限公司
            </div>
        </div>
    </div>
</body>
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/static/tpl/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/static/tpl/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/static/tpl/js/plugins/layer/layer.min.js"></script>
<script src="/static/tpl/js/hplus.min.js?v=4.1.0"></script>
<script src="/static/tpl/js/contabs.min.js"></script>
<script src="/static/tpl/js/plugins/pace/pace.min.js"></script>
<script type="text/javascript">

</script>

</html>

