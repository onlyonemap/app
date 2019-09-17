<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"D:\WWW\chitu_admin\public/../application/admin\view\info\rangeindex.html";i:1546918915;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
                        <h5>城配开通城市列表</h5>
                        <div class="ibox-tools">
                            <a class="dropdown-toggle"  href="/admin/info/addrange" style="color:#000">
                                <i class="fa fa-wrench" ></i> 添加城配运输城市
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row m-b-sm m-t-sm">
                            <div class="col-md-1">
                                <a href="/admin/info/costrange" id="loading-example-btn" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                            </div>
                            <div class="col-md-11">
                                <div class="input-group">
                                    <input type="text" value="" placeholder="请输入城市名称" id="provSelect1" class="form-control"> 
                                    <span class="input-group-btn">
                                        <button type="button" id="search" class="btn btn-primary">查询</button> 
                                    </span>
                                </div>
                            </div>
                        </div>
                             
                        <table class="footable table table-stripped toggle-arrow-tiny">
                            <thead>
                                <tr>
                                    <th data-toggle="true" class="text-left">ID</th>
                                    <th class="text-left">开通城市</th>
                                    <th class="text-left">添加时间</th>
                                    <th class="text-center">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <tr>
                                    <td class="text-left"><?php echo $vo['cost_id']; ?></td>
                                    <td class="text-left"><?php echo $vo['name']; ?></td>
                                    <td class="text-left"><?php echo $vo['addtime']; ?></td>
                                    <td class="text-center">
                                        <a href="/admin/info/edit?id=<?php echo $vo['cost_id']; ?>" class="btn btn-info"><i class="fa fa-paste"></i>编辑</a>
                                        
                                        <a data-href="/admin/info/delcity?del=1&id=<?php echo $vo['cost_id']; ?>" class="btn btn-danger confir"><i class="fa fa-paste"></i>删除</a>
                                        
                                    </td>
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
    <!-- jquery -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <!-- bootstrap -->
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <!-- footable -->
    <script src="/static/tpl/js/plugins/footable/footable.all.min.js"></script>
    <!-- layer -->
    <script src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <!-- 页面js -->
    <script type="text/javascript">
        $(document).ready(function() {
            // 初始话表格
            $('.footable').footable();
            // 查询
            $('#search').click(function(){
                var psel = document.getElementById("provSelect1");
                window.location.href='/admin/info/costrange?search='+psel.value;
            });
            // 删除
            $('.confir').click(function(){            	
            	var href = $(this).attr("data-href");
                layer.confirm('删除后数据将无法找回，确定要执行该操作吗？', {
                    btn: ['确定','取消'] //按钮
                }, function(){
                    window.location.href=href;
                }, function(){
                	
                });
                
            });
        });

    </script>
</body>
</html>
