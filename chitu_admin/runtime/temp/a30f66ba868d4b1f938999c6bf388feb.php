<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:79:"D:\WWW\chitu_admin\public/../application/admin\view\setting\updatedelivery.html";i:1567504483;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
            <h5><a href="javascript:history.back(-1);"><span class="glyphicon glyphicon-chevron-left"><b>返回</b></span></a></h5>
            <div class="ibox-tools">添加城市配送类别</div>
        </div>
        <div class="ibox-content">
            <form class="form-horizontal m-t" method="post" action="/admin/setting/savedelivery" id="commentForm">
                <div class="form-group">
                    <label class="col-sm-2 control-label">分类名称</label>
                    <div class="col-sm-6">
                        <input class="form-control" type="text" name="name" minlength="2" placeholder="<?php echo $list['name']; ?>" required="" />
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">是否上线 </label>
                    <div class="col-sm-10">
                        <div class="radio i-checks">
                            <label><input type="radio" value="2" name="status" <?php if(($list['status']==2)): ?> checked="checked" <?php endif; ?> ><i></i>否</label>
                        </div>
                        <div class="radio i-checks">
                            <label><input type="radio"  value="1" name="status" <?php if(($list['status']==1)): ?> checked="checked" <?php endif; ?> ><i></i> 是（选中）</label>
                        </div>
                    </div>
                </div>

                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <input type="hidden" name="cid" value="<?php echo $list['cid']; ?>">
                        <button class="btn btn-primary" type="submit">保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- jquery -->
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<!-- bootstrap -->
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
<!-- 全局js -->
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<!-- bootstrap -->
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
<!-- iCheck -->
<script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // radio 初始化
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    });
</script>
</body>
</html>
