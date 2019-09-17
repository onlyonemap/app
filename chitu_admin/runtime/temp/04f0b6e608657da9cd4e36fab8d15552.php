<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:69:"D:\WWW\chitu_admin\public/../application/admin\view\staff\detail.html";i:1564198218;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
                        <div class="ibox-tools">账户详情</div>
                    </div>
                    <div class="ibox-content">
                    	<div class="row">
                    		<div class="col-sm-2">用户名</div>
                    		<div class="col-sm-10"><?php echo $list['username']; ?></div>
                    	</div>
                    	<div class="hr-line-dashed"></div>

                    	<div class="row">
                    		<div class="col-sm-2">编号</div>
                    		<div class="col-sm-10"><?php echo $list['numbers']; ?></div>
                    	</div>
                    	<div class="hr-line-dashed"></div>
						
						<div class="row">
                    		<div class="col-sm-2">公司名称</div>
                    		<div class="col-sm-10"><?php echo $list['realname']; ?></div>
                    	</div>
                    	<div class="hr-line-dashed"></div>

                    	<div class="row">
                    		<div class="col-sm-2">手机号</div>
                    		<div class="col-sm-10"><?php echo $list['tel']; ?></div>
                    	</div>
                    	<div class="hr-line-dashed"></div>

                    	<div class="row">
                    		<div class="col-sm-2">性别</div>
                    		<div class="col-sm-10"><?php echo $list['sex']; ?></div>
                    	</div>
                    	<div class="hr-line-dashed"></div>

<!--                    	<div class="row">-->
<!--                    		<div class="col-sm-2">职位</div>-->
<!--                    		<div class="col-sm-10"><?php echo $list['role']; ?></div>-->
<!--                    	</div>-->
<!--                    	<div class="hr-line-dashed"></div>-->

                    	<div class="row">
                    		<div class="col-sm-2">微信号</div>
                    		<div class="col-sm-10"><?php echo $list['weixin']; ?></div>
                    	</div>
                    	<div class="hr-line-dashed"></div>

                    	<div class="row">
                    		<div class="col-sm-2">邮箱</div>
                    		<div class="col-sm-10"><?php echo $list['email']; ?></div>
                    	</div>
                    	<div class="hr-line-dashed"></div>

                    	<div class="row">
                    		<div class="col-sm-2">添加时间</div>
                    		<div class="col-sm-10"><?php echo date('Y-m-d H:m:s',$list['addtime']); ?></div>
                    	</div>
                    	<div class="hr-line-dashed"></div>

<!--                    	<div class="row">-->
<!--                    		<div class="col-sm-2">公司名称</div>-->
<!--                    		<div class="col-sm-10">赤途（上海）供应链管理有限公司</div>-->
<!--                    	</div>-->
<!--                    	<div class="hr-line-dashed"></div>-->

<!--                    	<div class="row">-->
<!--                    		<div class="col-sm-2">公司地址</div>-->
<!--                    		<div class="col-sm-10">上海市嘉定区金沙江西路1555弄380号2楼</div>-->
<!--                    	</div>-->
<!--                    	<div class="hr-line-dashed"></div>-->
                    </div>	
                </div>
            </div>
        </div>
    </div>
</body>

</html>
