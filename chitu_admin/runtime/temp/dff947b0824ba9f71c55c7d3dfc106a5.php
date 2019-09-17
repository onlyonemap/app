<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:73:"D:\WWW\chitu_admin\public/../application/admin\view\toreview\failure.html";i:1531291462;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
                <h5>提现审核失败列表</h5>
            </div>
            <div class="ibox-content">
                <div class="row m-b-sm m-t-sm">
                    <div class="col-md-1">
                        <a href="/admin/toreview/failure" id="loading-example-btn" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                    </div>

                    <div class="col-md-11">
                        <div class="input-group">
                            <input type="text" value="" placeholder="请输入用户账号" class="form-control" > 
                            <span class="input-group-btn">
                                <button type="button" id="search" class="btn btn-primary">查询</button>
                            </span>
                        </div>
                    </div>
                </div>
                            
                <table class="footable table table-stripped toggle-arrow-tiny">
                    <thead>
                        <tr>  
                            <th class="text-left">申请时间</th>
                            <th class="text-left">申请人</th>
                            <th class="text-left">手机号码</th>
                            <th class="text-left">申请提现金额</th>
                            <th class="text-left">应打款金额</th>
                            <th class="text-left">支付宝账号</th>
                            <th class="text-left">用户类型</th>
                            <th class="text-left" >申请属性</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>    
                            <td><?php echo date('Y-m-d H:i:s',$vo['start_time']); ?></td>
                            <td class="text-left"><?php echo $vo['realname']; ?></td>
                            <td class="text-left"><?php echo $vo['mobile']; ?></td>
                            <td class="text-left"><?php echo $vo['actual_money']; ?></td>
                            <td class="text-left"><?php echo $vo['money']; ?></td>
                            <td class="text-left"><?php echo $vo['account']; ?></td>
                            <td class="text-left"><?php if($vo['action_type'] == '1'): ?>司机<?php else: ?>货主<?php endif; ?></td>
                            <td class="text-left"><?php echo $vo['menu_type']; ?></td>
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
    <script type="text/javascript">
        // 初始话表格
        $('.footable').footable();
        // 查询
        $('#search').click(function(){
            var psel = document.getElementById("provSelect1");
            window.location.href='/admin/toreview/failure?search='+psel.value;
        });
    </script>
</body>

</html>
