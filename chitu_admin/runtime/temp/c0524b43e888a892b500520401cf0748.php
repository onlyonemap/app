<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:82:"D:\WWW\chitu_admin\public/../application/admin\view\carloadorder\prouserorder.html";i:1567049182;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


    <link href="/static/tpl/css/plugins/datapicker/datepicker3.css" rel="stylesheet" />
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/bootstrap-table.min.css">
    <link rel="stylesheet" type="text/css" href="/static/tpl/css/common/search.css"/>
    <link rel="stylesheet" type="text/css" href="/static/tpl/css/order_table.css" />
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="ibox">
            <div class="ibox-title">
                <h5>整车订单列表</h5>
            </div>
            <div class="ibox-content">
                <div class="row m-b-sm m-t-sm">
                    <div class="col-md-1">
                        <a href="/admin/carloadorder/prouserorder" id="loading-example-btn" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group date date-view-three" data-provide="datepicker">
                            <div class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </div>
                            <input type="text" name="starttime" id="starttime" value="<?php echo isset($_GET['starttime'])?$_GET['starttime']:'' ?>" class="form-control" placeholder="搜索下单开始时间">
                        </div>
                    </div>

                    <div class="col-sm-1">
                        <p class="form-control-static text-center">----</p>
                    </div>

                    <div class="col-sm-3">
                        <div class="input-group date date-view-three" data-provide="datepicker">
                            <div class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </div>
                            <input type="text" id="endtime" name="endtime" value="<?php echo isset($_GET['endtime'])?$_GET['endtime']:'' ?>" class="form-control" placeholder="搜索下单结束时间">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="请输入订单号码/下单人姓名/电话号码" id="provSelect1" class="form-control"> 
                            <span class="input-group-btn">
                                <button type="button" id="search" class="btn btn-primary">查询</button>
                            </span>
                        </div>
                    </div>
                </div>
                <table class="table" data-toggle="table" data-height="800">
                    <thead>
                        <tr>
                            <th class="td-input"></th>
                            <th class="text-left" data-sortable="true">订单号码</th>
                            <th class="text-left" data-sortable="true">线路类别</th>
                            <th class="text-left" data-sortable="true">线路</th>
                            <th class="text-left" data-sortable="true">下单时间</th>
                            <th class="text-left" data-sortable="true">下单客户</th>
                            <th class="text-left" data-sortable="true">装货日期</th>
                            <th class="text-left" data-sortable="true">承运人</th>
                            <th class="text-left" data-sortable="true">接单状态</th>
                            <th class="text-left" data-sortable="true">应收状态</th>
                            <th class="text-right" data-sortable="true">应收运费(元)</th>
                            <th class="text-right" data-sortable="true">实收运费(元)</th>
                            <th class="text-left" data-sortable="true">应付状态</th>
                            <th class="text-right" data-sortable="true">应付运费</th>
                            <th class="text-right" data-sortable="true">实付运费</th>
                            <th class="text-right" data-sortable="true">业务员</th>
                            <th class="text-right">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td>
                                <input type="checkbox" value="<?php echo $vo['uoid']; ?>" class="i-checks check-list" name="id" />
                            </td>
                            <td class="text-left"><?php echo $vo['ordernumber']; ?></td>
                            <td class="text-left">
                            <?php if(($vo['order_type'] == 3)): ?>
                            <span class="label label-danger">市内</span>
                            <?php else: ?>
                            <span class="label label-primary">城际</span>
                            <?php endif; ?>
                            </td>
                            <?php if(($vo['order_type'] == 3)): ?>
                            <td class="text-left"><?php echo $vo['startcity']; ?></td>
                            <?php else: ?>
                            <td class="text-left"><?php echo $vo['startcity']; ?>-<?php echo $vo['endcity']; ?></td>
                            <?php endif; ?>

                            <td class="text-left"><?php echo date('Y-m-d H:i',$vo['addtime']); ?></td>
                            <td class="text-left"><?php echo $vo['name']; ?> <?php echo $vo['phone']; ?></td>
                            <td class="text-left"><?php echo $vo['loaddate']; ?></td>
                            <td class="text-left"><?php echo $vo['drivername']; ?> <?php echo $vo['mobile']; ?></td>
                            <td class="text-left">
                                <?php switch($vo['orderstate']): case "1": ?><span class="label label-danger">未接单</span><?php break; case "2": ?><span class="label label-warning">已接单</span><?php break; case "3": ?><span class="label label-primary">已完成</span><?php break; case "4": ?><span class="label label-danger">已取消</span><?php break; case "5": ?><span class="label label-warning">已提货</span><?php break; case "6": ?><span class="label label-warning">已配送</span><?php break; case "7": ?><span class="label label-warning">已超时</span><?php break; default: endswitch; ?>
                            </td>
                            <td class="text-left">
                                <?php if(($vo['paystate_str']==1)): ?>
                                <span class="label label-warning">未支付</span>
                                <?php elseif(($vo['paystate_str']==2)): ?>
                                <span class="label label-primary">已支付</span>
                                <?php elseif(($vo['paystate_str']==3)): ?>
                                <span class="label label-warning">信用支付</span>
                                <?php elseif(($vo['paystate_str']==4)): ?>
                                <span class="label label-danger">提货付款</span>
                                <?php elseif(($vo['paystate_str']==5)): ?>
                                <span class="label label-danger">货到付款</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if(($vo['type']==5)): ?>
                                    <?php echo $vo['down_price']; else: ?>
                                    <?php echo $vo['paymoney']; endif; ?>
                            </td>
                            <td class="text-right"><?php echo $vo['referprice']; ?></td>
                            <td class="text-left">
                                <?php if(($vo['driver_mess']==1)): ?>
                                <span class="label label-warning">未支付</span>
                                <?php elseif(($vo['driver_mess']==2)): ?>
                                <span class="label label-primary">已支付</span>
                                <?php elseif(($vo['driver_mess']==3)): ?>
                                <span class="label label-warning">信用支付</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if(($vo['type']==5)): ?>
                                    0
                                <?php else: ?>
                                    <?php echo $vo['driver_payment']; endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if(($vo['type']==5)): ?>
                                    0
                                <?php else: ?>
                                    <?php echo $vo['driver_pay']; endif; ?>
                            </td>
                            <td class="text-right"><?php echo $vo['salesman']; ?></td>
                            <td class="text-right">
                                <a href="/admin/carloadorder/prodetails?id=<?php echo $vo['uoid']; ?>" class="btn btn-info"><i class="fa fa-search"></i>查看</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <?php echo $page; ?>
                <div class="row m-t-sm">
                    <div class="col-sm-2" style="padding-left: 23px;">
                        <input class="i-checks" id="allCheck" type="checkbox" />
                        <label for="allCheck">全选</label>
                    </div>
                    <div class="col-sm-10">
                        <a class="btn btn-sm btn-primary"  id="execlcheck" href="javascript:;">导出</a>
                        <a class="btn btn-sm btn-primary"  id="unpay" href="javascript:;">导出未支付订单</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <!-- bootstrap table -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/bootstrap-table.min.js"></script>
    <!-- bootstrap table 语言包 -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/locale/bootstrap-table-zh-CN.min.js"></script>
     <!-- 日期插件 -->
    <script type="text/javascript" src="/static/tpl/js/plugins/datapicker/bootstrap-datepicker.js"></script>
    <!-- 调用日期插件 -->
    <script type="text/javascript" src="/static/tpl/js/callDatepicker.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // 全选
            $('#allCheck').change(function (argument) {
                if(this.checked){ // 全选
                    $('input[name="id"]').prop("checked",true); 
                }else{ // 全不选
                    $('input[name="id"]').prop("checked",false);  
                }
            });
            // 查询
            $('#search').click(function(){
                var psel = document.getElementById("provSelect1");
                var endtime = document.getElementById("endtime").value;
                var starttime = document.getElementById("starttime").value;
                window.location.href='/admin/carloadorder/prouserorder?search='+psel.value+'&starttime='+starttime+'&endtime='+endtime;
            });
            $("#execlcheck").click(function(){
                if(confirm("确认要导出订单吗？")) {
                    var endtime = document.getElementById("endtime").value;
                    var starttime = document.getElementById("starttime").value;
                    var chk_value = new Array();
                    
                    var length = $('input[name="id"]:checked').length;
                    if (length<=0) {
                        alert('请勾选数据！！'); return false;
                    };
                    //订单ID
                    $('input[name="id"]:checked').each(function(){
                        chk_value.push($(this).val());//将选中的值添加到数组chk_value中
                    });
                   
                    window.location.href='/admin/execl/getcarclient?id='+chk_value+'&starttime='+starttime+'&endtime='+endtime;
                 }else{
                    return false; 
                }
            });
            $("#unpay").click(function(){
                if(confirm("确认要导出下单未支付用户吗？")) {
                    window.location.href='/admin/execl/unpay?ordertype=2';
                 }else{
                    return false; 
                }
             });
        });

    </script>
</body>
</html>
