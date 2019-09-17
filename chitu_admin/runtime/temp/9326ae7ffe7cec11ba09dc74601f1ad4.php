<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"D:\WWW\chitu_admin\public/../application/admin\view\driver\carindex.html";i:1533783227;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
    <div class="wrapper wrapper-content  animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title">
                <h5>车辆列表</h5>
                <div class="ibox-tools">    
                    <a href="/admin/driver/addcarcategory" style="color:#000">
                        <span class="glyphicon glyphicon-plus"></span>
                        <span>添加</span>
                    </a>    
                </div>
            </div>
            <div class="ibox-content">
                <div class="row m-b-sm m-t-sm">
                    <div class="col-md-1">
                        <a href="/admin/driver/carindex" id="loading-example-btn" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                    </div>
                    <div class="col-md-11">
                        <div class="input-group">
                            <input type="text" placeholder="请输入车牌号、姓名、个体司机电话、公司名称" id="provSelect1" class="form-control" />  
                            <span class="input-group-btn">
                                <button type="button" id="search" class="btn btn-primary">查询</button>
                            </span>
                        </div>
                    </div>
                </div>
                
                <table class="footable table" >
                    <thead>
                        <tr>
                            <th class="text-left">车牌号</th>
                            <th class="text-left">车型</th>
                            <th class="text-left">注册日期</th>
                            <th class="text-left">状态</th>
                            <th class="text-left">归属</th>
                            <th class="text-center">联系方式</th>
                            <th class="text-center">审核状态</th>
                            <th class="text-center">添加时间</th>
                            <th class="text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td class="text-left"><?php echo $vo['carnumber']; ?></td>
                            <td class="text-left"><?php echo $vo['carparame']; ?></td>
                            <td class="text-left"><?php echo $vo['car_age']; ?></td>
                            <td class="text-left">
                                <?php if(($vo['car_grade'] == 1)): ?>
                                    <span class="label label-info">个体</span>
                                <?php else: ?>
                                    <span class="label label-primary">公司</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-left"><?php echo $vo['name']; ?></td>
                            <td class="text-center"><?php echo $vo['phone']; ?></td>
                            
                            <td class="text-center">
                                <?php if(($vo['status'] == 1)): ?>
                                <span class="badge badge-danger">未审核</span>
                                <?php elseif(($vo['status'] == 2)): ?>
                                <span class="badge badge-info">已通过</span>
                                <?php elseif(($vo['status'] == 3)): ?>
                                <span class="badge badge-primary">未通过</span>
                                <?php elseif(($vo['status'] == 4)): ?>
                                <span class="badge badge-warning">审核中</span>
                                <?php endif; ?>                                
                            </td>
                            <td class="text-center"><?php echo date('Y-m-d H:i:s',$vo['addtime']); ?></td>
                            <td class="text-center">
                                <a href="/admin/driver/updatecarcategory?id=<?php echo $vo['ccid']; ?>" class="btn btn-primary btn-sm">修改</a>
                                <a href="/admin/driver/cardetails?id=<?php echo $vo['ccid']; ?>" class="btn btn-info btn-sm">详情</a>
                                <a data-href="/admin/driver/delcar_mess?id=<?php echo $vo['ccid']; ?>" class="btn btn-danger btn-sm delete">删除</a>
                            </td>
                        </tr>
                       <?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <?php echo $page; ?>
            </div>
        </div>
    </div>

    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/static/tpl/js/plugins/footable/footable.all.min.js"></script>
    <script src="/static/tpl/js/plugins/layer/layer.min.js"></script> 
    <script type="text/javascript">
        $(function () {
            // 初始话表格
            $('.footable').footable();
            // 查询
            $('#search').click(function(){
                var psel = document.getElementById("provSelect1");
                window.location.href='/admin/driver/carindex?search='+psel.value;
            });
            // 删除
            $('.delete').click(function(){               
                var url = $(this).attr("data-href");
                layer.confirm('删除后数据将无法找回，确定要执行该操作吗？', {
                    btn: ['确定','取消'] //按钮
                }, function(){
                    window.location.href=url;
                }, function(){
                            
                });
                        
            });
        });
    </script>

</body>

</html>
