<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:71:"D:\WWW\chitu_admin\public/../application/admin\view\carriers\index.html";i:1524793647;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
    <link rel="stylesheet" type="text/css" href="/static/tpl/css/plugins/layer/layer.css">
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>承运商列表</h5>
                <div class="ibox-tools">
                    <a class="dropdown-toggle"  href="/admin/carriers/addcarriers" style="color:#000">
                        <span class="glyphicon glyphicon-plus"></span>
                        <span>添加</span>
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                <div class="row m-b-sm m-t-sm">
                    <div class="col-md-1">
                        <a href="/admin/carriers/index" id="loading-example-btn" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                    </div>
                    <div class="col-md-11">
                        <div class="input-group">
                            <input type="text" value="<?php echo isset($_GET['search'])?$_GET['search']:''?>" placeholder="请输入公司名称" id="provSelect1" class="form-control"> 
                            <span class="input-group-btn">
                                <button type="button" id="search" class="btn btn-primary">查询</button>
                            </span>
                        </div>
                    </div>
                </div>

                   
                <table class="footable table table-stripped toggle-arrow-tiny">
                    <thead>
                    <tr>
                        <th class="text-left">公司名称</th>
                        <th class="text-left">管理员</th>
                        <th class="text-left">用户名</th>
                        <th class="text-left">性别</th>
                        <th class="text-left">手机号</th>
                        <th class="text-left" data-hide="all">提货服务区域</th>
                        <th class="text-left" data-hide="all">干线服务区域</th>
                        <th class="text-left" data-hide="all">配送服务区域</th>
                        <th class="text-left">添加时间</th>
                        <th class="text-center">操作</th> 
                    </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td class="text-left"><?php echo $vo['name']; ?></td>
                            <td class="text-left"><?php echo $vo['realname']; ?></td>                                  
                            <td class="text-left"><?php echo $vo['username']; ?></td>
                            <td class="text-left"><?php if(($vo['sex']==1)): ?>男<?php else: ?>女<?php endif; ?></td>
                            <td class="text-left"><?php echo $vo['mobile']; ?></td>
                            <td>
                                <?php if(isset($vo['ti'])): ?>
                                <table class=" table">
                                    <thead>
                                        
                                        <th width="20%">城市</th>
                                        <th width="20%">提货基准价</th>
                                        <th width="20%">提货费用率</th>
                                        <th width="20%"></th>
                                    </thead>
                                    <tbody>
                                        <?php if(is_array($vo['ti']) || $vo['ti'] instanceof \think\Collection || $vo['ti'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['ti'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$voti): $mod = ($i % 2 );++$i;?>
                                        <tr>
                                            <td width="20%"><?php echo $voti['province']; ?></td>
                                            <td width="20%"><?php echo $voti['price']; ?></td>
                                            <td width="20%"><?php echo $voti['rate']; ?></td>
                                            <td width="20%"></td> 
                                        <tr/>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </tbody>
                                </table>
                               <?php endif; ?>
                            </td>
                            <td>
                                <?php if(isset($vo['shift'])): ?>
                                <table class=" table">
                                    <thead>
                                        <th width="20%">班次号</th>
                                        <th width="20%">出发城市</th>
                                        <th width="20%">终点城市</th>
                                        <th width="20%"></th>
                                    </thead>
                                    <tbody>
                                        <?php if(is_array($vo['shift']) || $vo['shift'] instanceof \think\Collection || $vo['shift'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['shift'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sh): $mod = ($i % 2 );++$i;?>
                                        <tr>
                                            <td width="20%"><?php echo $sh['shiftnumber']; ?></td>  
                                            <td width="20%"><?php echo $sh['shifstartline']; ?></td> 
                                            <td width="20%"><?php echo $sh['shifendline']; ?></td>
                                            <td width="20%"></td> 
                                        </tr>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </tbody>
                                </table>
                                <?php endif; ?>  
                            </td>
                            <td>
                                <?php if(isset($vo['pei'])): ?>
                                <table class=" table">
                                    <thead>
                                        <th width="20%">城市</th>
                                        <th width="20%">配送基准价</th>
                                        <th width="20%">配送费用率</th>
                                        <th width="20%"></th>
                                        <!-- <th width="20%">仓库地址</th> -->
                                    </thead>
                                    <tbody>
                                        <?php if(is_array($vo['pei']) || $vo['pei'] instanceof \think\Collection || $vo['pei'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['pei'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vopei): $mod = ($i % 2 );++$i;?>
                                        <tr>
                                            <td width="20%"><?php echo $vopei['province']; ?></td>
                                            <td width="20%"><?php echo $vopei['price']; ?></td>  
                                            <td width="20%"><?php echo $vopei['rate']; ?></td>
                                            <td width="20%"></td>
                                           <!--  <td width="20%"><?php if(isset($vopei['can']) !=''): if(is_array($vopei['can']) || $vopei['can'] instanceof \think\Collection || $vopei['can'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vopei['can'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vocan): $mod = ($i % 2 );++$i;if(isset($vocan['can_address']) !=''): ?>
                                                <?php echo $vocan['can_address']; endif; endforeach; endif; else: echo "" ;endif; endif; ?> 
                                            </td> -->
                                            
                                        <tr/>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </tbody>
                                </table>
                                <?php endif; ?>                           
                            </td>    
                            <td class="text-left"><?php echo date('Y-m-d',$vo['addtime']); ?></td>
                            <td class="text-center">
                                <a class="btn btn-info" href="/admin/carriers/detail?cid=<?php echo $vo['cid']; ?>" >
                                    <span class="glyphicon glyphicon-file"></span>
                                    <span>详情</span>
                                </a>
                                <div class="btn-group">
                                    <button data-toggle="dropdown" class="btn btn-info dropdown-toggle">操作 <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="addplace" data-cid="<?php echo $vo['cid']; ?>">添加提配区域</a>
                                        </li>
                                        <li>
                                            <a class="addround" data-cid="<?php echo $vo['cid']; ?>">添加周边提货城市</a>
                                        </li>
                                        <li>
                                            <a href="/admin/carriers/carraddshift?cid=<?php echo $vo['cid']; ?>" class="addline">添加干线</a>
                                        </li>
                                    </ul>
                                </div>
                                <a href="/admin/carriers/updatecarriers?cid=<?php echo $vo['cid']; ?>" class="btn btn-warning">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                    <span>修改</span>
                                </a>
                                <a class="btn btn-danger del" data-cid="<?php echo $vo['cid']; ?>">
                                    <span class="glyphicon glyphicon-trash"></span>
                                    <span>删除</span>
                                </a>
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
    <!-- 遮罩提示 -->
    <script type="text/javascript" src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.footable').footable();
            // 查询
            $('#search').click(function(){
                var psel = document.getElementById("provSelect1");
                window.location.href='/admin/carriers/index?search='+psel.value;
            });
            // 删除
            $('.del').click(function () {
                var cid = $(this).attr('data-cid');

                layer.confirm('删除后数据将无法找回，确定要执行该操作吗？', {
                    btn: ['确定','取消'] //按钮
                }, function(){
                    window.location.href="/admin/carriers/delcom?cid="+cid;
                }, function(){
                    
                });
            })
            //添加提配服务区域
            $(".addplace").click(function(){
               //var lineid = $(this).attr('data-cid');
               var comid = $(this).attr('data-cid');
               layer.open({
                  type: 2,
                  title:"提配服务区域",
                  area: ['850px', '750px'],
                  fixed: false, //不固定

                  maxmin: true,
                  shadeClose: true,
                  content: '/admin/company/updategan?id='+comid
                });
            });

             //添加提配服务区域
            $(".addround").click(function(){
               //var lineid = $(this).attr('data-cid');
               var comid = $(this).attr('data-cid');
               layer.open({
                  type: 2,
                  title:"添加周边提货区域",
                  area: ['850px', '750px'],
                  fixed: false, //不固定

                  maxmin: true,
                  shadeClose: true,
                  content: '/admin/carriers/addround?id='+comid
                });
            });
            
        });
    </script>
</body>
</html>
