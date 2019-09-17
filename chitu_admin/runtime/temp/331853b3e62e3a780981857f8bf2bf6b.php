<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:85:"D:\WWW\chitu_admin\public/../application/admin\view\advertisement\adventClassAdd.html";i:1534735541;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><a href="javascript:history.back(-1);"><span class="glyphicon glyphicon-chevron-left"><b>返回</b></span></a></h5>
                <div class="ibox-tools">添加广告类别</div>
            </div>
            <div class="ibox-content">
                <form class="form-horizontal m-t" method="post" action="/admin/advertisement/addadventClass" id="commentForm">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">分类名称</label>
                        <div class="col-sm-10">
                            <input class="form-control" type="text" name="cate_name" minlength="2"  required="" />
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">父级分类</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="cate_parent">
                            	<option value="">---请选择---</option>
                            	<?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <option value="<?php echo $vo['id']; ?>" ><?php echo $vo['cate_name']; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">广告类型</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="cate_type">
                            	<option value="1">广告类型</option>
                            	<option value="2">文章类型</option>
                            </select>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                   
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
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
</body>
</html>
