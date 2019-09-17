<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:73:"D:\WWW\chitu_admin\public/../application/admin\view\setting\todetail.html";i:1566727306;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>
                        <a href="javascript:history.back(-1);"><span class="glyphicon glyphicon-chevron-left"><b>返回</b></span></a>
                    </h5>
                    <div class="ibox-tools">开通城市详情</div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-2">城市名</div>
                        <div class="col-sm-10"><?php echo $list['name']; ?></div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">每小时价格</div>
                        <div class="col-sm-10"><?php echo $list['scale_hour']; ?></div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">起步价系数</div>
                        <div class="col-sm-10"><?php echo $list['start_fare']; ?></div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-sm-2">起步价包含公里数</div>
                        <div class="col-sm-10"><?php echo $list['scale_klio']; ?></div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">单公里价格系数</div>
                        <div class="col-sm-10"><?php echo $list['scale_price']; ?></div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">状态</div>
                        <div class="col-sm-10"><?php echo $list['delstate']; ?></div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">添加时间</div>
                        <div class="col-sm-10"><?php echo $list['addtime']; ?></div>
                    </div>
                    <div class="hr-line-dashed"></div>

                </div>
            </div>
        </div>
    </div>
</div>
</body>

</html>
