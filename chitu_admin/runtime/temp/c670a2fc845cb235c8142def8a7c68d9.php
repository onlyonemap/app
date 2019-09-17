<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:82:"D:\WWW\chitu_admin\public/../application/admin\view\advertisement\adventClass.html";i:1534737083;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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


    <link rel="stylesheet" type="text/css" href="/static/tpl/css/common/search.css"/>
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title">
                <h5>广告类别</h5>
                <div class="ibox-tools">
                    <a href="/admin/advertisement/adventClassAdd">
                        <i class="fa fa-plus"> 添加 </i>
                    </a>
                </div>
            </div>
            <div class="ibox-content">
            	<!-- search start -->
				<div class="row m-b-sm m-t-sm">
                    <div class="col-md-1">
                        <a href="/admin/advertisement/adventClass" class="btn btn-white btn-sm"><i class="fa fa-refresh"> 刷新 </i></a>
                    </div>
                    <div class="col-md-11">
                        <div class="input-group">
                            <input class="form-control" id="provSelect1" type="text" placeholder="请输入分类名称" /> 
                            <span class="input-group-btn">
                                <button type="button" id="search" class="btn btn-primary">查询</button>
                            </span>
                        </div>
                    </div>
                </div>
                <!-- search end -->
		
                <table class="footable table table-stripped toggle-arrow-tiny">
                    <thead>
                        <tr>
                            <th class="text-left">分类名称</th>
                            <th class="text-left">父级分类</th>
                            <th class="text-left">广告类型</th>
                            <th class="text-left">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td class="text-left"><?php echo $vo['cate_name']; ?></td>
                            <td class="text-left"><?php echo $vo['cate_parent']; ?></td>
                            <td class="text-left"><?php echo $vo['cate_type']; ?></td>
                            <td class="text-left">
                             	<a href="/admin/advertisement/adventClassUpdate?id=<?php echo $vo['id']; ?>" class="btn btn-info"><i class="fa fa-paste"></i>编辑</a>
                            </td>
                        </tr>
                       <?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- 全局 -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <!-- bootstrap -->
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <!-- layer -->
    <script src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <!-- 自定义js -->
    <script type="text/javascript">
    	// 初始话表格
        $('.footable').footable();
        // 查询
        $('#search').click(function(){
            var psel = document.getElementById("provSelect1");
            window.location.href='/admin/advertisement/adventClass?search='+psel.value;
        });
        // 删除
    </script>
</body>

</html>
