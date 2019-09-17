<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:71:"D:\WWW\chitu_admin\public/../application/admin\view\shift\template.html";i:1523265439;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


    <link href="/static/tpl/css/plugins/footable/footable.core.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="/static/tpl/css/common/search.css"/>
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>干线班次模板</h5>
                        <div class="ibox-tools"> 
                            <a class="dropdown-toggle"  href="/admin/shift/addshift" style="color:#000">
                                <span class="glyphicon glyphicon-plus"></span>
                                <span>添加</span>
                            </a>   
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row m-b-sm m-t-sm">
                            <div class="col-md-1">
                                <a href="/admin/shift/template" id="loading-example-btn" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                            </div>
                            <div class="col-md-11">
                                <div class="input-group">
                                     <input type="text" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="请输入班次号/公司名称" id="provSelect1" class="form-control">
                                    <span class="input-group-btn ">
                                        <button type="button" id="search" class="btn btn-primary">查询</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <table class="footable table table-stripped toggle-arrow-tiny">
                            <thead>
                                <tr>
                                    <th data-toggle="true" class="text-left">公司名称</th>
                                    <th data-hide="all"></th>
                                    <th class="text-right">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <tr>
                                    <td class="text-left"><?php echo $vo['companyname']; ?></td>
                                    <td>
                                        <table class="table">
                                            <thead>
                                                <th>始发城市</th>
                                                <th>终点城市</th>
                                                <th>班次号</th>
                                                <th>操作</th>
                                            </thead>
                                            <?php if(!empty($vo['shift'])): if(is_array($vo['shift']) || $vo['shift'] instanceof \think\Collection || $vo['shift'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['shift'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                            <tr>
                                                <td><?php echo $v['start']; ?></td>
                                                <td><?php echo $v['end']; ?></td>
                                                <td><?php echo $v['shiftnumber']; ?></td>
                                                <td>
                                                    <a href="/admin/shift/uptemplate?id=<?php echo $v['sid']; ?>" class="btn btn-warning">
                                                        <span class="glyphicon glyphicon-pencil"></span>
                                                        <span>编辑</span>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                        </table>
                                   </td>
                                    <td class="text-right">查看模板 </td>
                                </tr>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table>
                            <?php echo $page; ?>
                        </div>
                    </div>
                </div>
            </div>
          
        </div>
    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/static/tpl/js/plugins/footable/footable.all.min.js"></script>

   
    <script>
        $(document).ready(function() {
            // 初始话表格
            $('.footable').footable();
            // 查询
            $('#search').click(function(){
                var psel = document.getElementById("provSelect1");
                window.location.href='/admin/shift/template?search='+psel.value;
            });
        });

    </script>
</body>

</html>
