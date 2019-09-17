<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:67:"D:\WWW\chitu_admin\public/../application/admin\view\staff\list.html";i:1564196739;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>员工列表</h5>
                <div class="ibox-tools">
                    <a class="dropdown-toggle"  href="/admin/staff/toadd" style="color:#000">
                        <span class="glyphicon glyphicon-plus"></span>
                        <span>添加</span>
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                <div class="row m-b-sm m-t-sm">
                    <div class="col-md-1">
                        <a href="/admin/staff/index" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                    </div>
                    <div class="col-md-11">
                        <div class="input-group">
                            <input type="text" placeholder="请输入姓名" value="" id="provSelect1" class="form-control" /> 
                            <span class="input-group-btn">
                                <button type="button" id="search" class="btn btn-primary">查询</button>
                            </span>
                        </div>
                    </div>
                </div>
                 
                <table class="footable table table-stripped toggle-arrow-tiny">
                    <thead>
                        <tr>
                            <th class="text-left">编号</th>
                            <th class="text-left">用户名</th>
                            <th class="text-left">公司名称</th>
                            <th class="text-left">性别</th>
                            <th class="text-left">联系电话</th>
                            <th class="text-left">微信号</th>
                            <th class="text-left">邮箱</th>
                            <th class="text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td class="text-left"><?php echo $vo['numbers']; ?></td>
                            <td class="text-left"><?php echo $vo['username']; ?></td>
                            <td class="text-left"><?php echo $vo['realname']; ?></td>
                            <td class="text-left">
                                <?php if($vo['sex'] == '2'): ?> 女 <?php else: ?> 男 <?php endif; ?>
                            </td>
                            <td class="text-left"><?php echo $vo['tel']; ?></td>
                            <td class="text-left"><?php echo $vo['weixin']; ?></td>
                            <td class="text-left"><?php echo $vo['email']; ?></td>
                            <?php if($grade['grade'] == 1): ?>
                            <td class="text-center">
                                <a href="/admin/staff/todetail?id=<?php echo $vo['aid']; ?>" class="btn btn-info">
                                    <span class="glyphicon glyphicon-file"></span>
                                    <span>详情</span>
                                </a>
                                <a href="/admin/staff/toupdate?id=<?php echo $vo['aid']; ?>" class="btn btn-warning">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                    <span>修改</span>
                                </a>
                                <a data-ur="/admin/staff/delete?id=<?php echo $vo['aid']; ?>" class="btn btn-danger confir">
                                    <span class="glyphicon glyphicon-trash"></span>
                                    <span>删除</span>
                                </a>
                                <a  class="btn btn-warning btn-sm info" onClick="change_password('添加权限','<?php echo url('admin/staff/addauth'); ?>?aid=<?php echo $vo['aid']; ?>','0','600','500')" title="授权">
                                    <span class="glyphicon glyphicon-filter"></span>
                                    <span>授权</span>
                                </a>
                            </td>
                            <?php else: ?>
                            <td class="text-center">
                                <a href="/admin/staff/todetail?id=<?php echo $vo['aid']; ?>" class="btn btn-info">
                                    <span class="glyphicon glyphicon-file"></span>
                                    <span>详情</span>
                                </a>
                                <a href="/admin/staff/toupdate?id=<?php echo $vo['aid']; ?>" class="btn btn-warning">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                    <span>修改</span>
                                </a>
                                <a data-ur="/admin/staff/delete?id=<?php echo $vo['aid']; ?>" class="btn btn-danger confir">
                                    <span class="glyphicon glyphicon-trash"></span>
                                    <span>删除</span>
                                </a>
                            </td>
                            <?php endif; ?>
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
    <script type="text/javascript" src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <script type="text/javascript" src="__STATIC__/tpl/js/layer.js"></script>
    <script type="text/javascript" src="__STATIC__/tpl/js/laypage.js"></script>
    <script type="text/javascript" src="__STATIC__/tpl/js/h-ui/js/H-ui.min.js"></script>
    <script type="text/javascript" src="__STATIC__/tpl/js//h-ui.admin/js/H-ui.admin.js"></script>
    <script>
        $(document).ready(function() {
            // 初始话表格
            $('.footable').footable();
            // 查询
            $('#search').click(function(){
                var psel = document.getElementById("provSelect1");
                window.location.href='/admin/staff/index?search='+psel.value;
            });
            // 删除
            $('.confir').click(function(){
				var ur = $(this).attr("data-ur");
                layer.confirm('删除后数据将无法找回，确定要执行该操作吗？', {
                    btn: ['确定','取消'] //按钮
                }, function(){
                    window.location.href=ur;
                }, function(){
                	
                });
                
            });
        });
        function change_password(title,url,id,w,h){
            layer_show(title,url,w,h);
        }
    </script>
</body>
</html>
