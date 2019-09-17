<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:68:"D:\WWW\chitu_admin\public/../application/admin\view\shift\index.html";i:1564743305;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>干线班次</h5>
                        <div class="ibox-tools">
                            <a class="dropdown-toggle"  href="/admin/shift/addshift" style="color: #000;">
                                <span class="glyphicon glyphicon-plus"></span>
                                <span>添加</span>
                            </a>
                            <a class="dropdown-toggle"  href="/admin/shift/addconnect" style="color: #000;">
                                <span class="glyphicon glyphicon-plus"></span>
                                <span>添加中转线路</span>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row m-b-sm m-t-sm">
                            <div class="col-md-1">
                                <a href="/admin/shift/index" class="btn btn-white btn-sm"><i class="fa fa-refresh"></i> 刷新</a>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <select class="selectpicker startcity" data-live-search="true" data-live-search-placeholder="搜索" data-actions-box="false">
                                        <option value='A'>请输入始发城市</option>
                                    　　  <?php if(is_array($address) || $address instanceof \think\Collection || $address instanceof \think\Paginator): $i = 0; $__LIST__ = $address;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    　　　 <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                    　　  <?php endforeach; endif; else: echo "" ;endif; ?>　
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <select class="selectpicker endcity" data-live-search="true" data-live-search-placeholder="搜索" data-actions-box="false">
                                        <option value='A'>请输入终点城市</option>
                                        <?php if(is_array($address) || $address instanceof \think\Collection || $address instanceof \think\Paginator): $i = 0; $__LIST__ = $address;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                                    　　　 <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                    　　  <?php endforeach; endif; else: echo "" ;endif; ?>　
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group">
                                     <input type="text" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="公司名称" id="provSelect1" class="form-control"> 
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
                                    <th class="text-left">公司名称</th>
                                    <th class="text-left">始发城市</th>
                                    <th class="text-left">终点城市</th>
                                    <th class="text-left">班次号</th>
                                    <th class="text-left">状态</th>
                                    <th class="text-left">折扣</th>

                                    <th data-hide="all">时效（D）</th>
                                    <th data-hide="all">干线基准价(kg)</th>

                                    <th data-hide="all">抛货基准价(m³)</th>
                                    <th data-hide="all">自行送货截止时间</th>
                                    <th data-hide="all">到达时间</th>
                                    <th class="text-center">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <tr data-type="<?php echo $vo['shiftstate']; ?>">
                                    <td class="text-left"><?php echo $vo['sid']; ?></td>
                                    <td class="text-left"><?php echo $vo['name']; ?></td>
                                    <td class="text-left"><?php echo $vo['start']; ?></td>
                                    <td class="text-left"><?php echo $vo['end']; ?></td>
                                    <td class="text-left"><?php echo $vo['shiftnumber']; ?></td>
                                    <td class="text-left">
                                        <?php if(($vo['whethertoopen'] ==1)): ?>
                                            开启
                                        <?php else: ?>
                                            关闭
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-left">
                                        <?php if(($vo['discount'] !=1)): ?>
                                            <?php echo $vo['discount']; endif; ?>
                                    </td>
                                    <td data-id="<?php echo $vo['trunkaging']; ?>"><?php echo $vo['trunkaging']; ?></td>
                                    <td data-number="<?php echo $vo['price']; ?>"><?php echo $vo['price']; ?></td>
                                    
                                    <td><?php echo $vo['eprice']; ?></td>
                                   
                                    <?php if(($vo['shiftstate']=='1')): ?>
                                    <td><?php echo $vo['selfdeliverydeadline']; ?></td>
                                    <td><?php echo $vo['arrivetimestart']; ?> - <?php echo $vo['arrivetimeend']; ?></td>
                                    <?php else: ?>
                                    <td><?php echo $vo['pmoney']; ?></td>
                                    <td><?php echo $vo['smoney']; ?></td>
                                    <?php endif; ?>
                                    <td class="text-center">
                                        <?php if(($vo['shiftstate']=='1')): ?>
                                        <a href="/admin/shift/updateshift?id=<?php echo $vo['sid']; ?>" class="btn btn-info">
                                            <span class="glyphicon glyphicon-pencil"></span>
                                            <span>修改</span>
                                        </a>
                                        <?php else: ?>
                                        <a class="btn btn-default">
                                            <span class="glyphicon glyphicon-pencil"></span>
                                            <span>修改</span>
                                        </a>
                                        <?php endif; if(($vo['whethertoopen'] == 1)): ?>
                                        <a href="/admin/shift/delcom?id=<?php echo $vo['sid']; ?>&del=1" class="btn btn-warning">
                                            <span class="glyphicon glyphicon-off"></span>
                                            <span>关闭</span>
                                        </a>
                                        <?php else: ?>
                                        <a href="/admin/shift/delcom?id=<?php echo $vo['sid']; ?>&del=2" class="btn btn-primary">
                                            <span class="glyphicon glyphicon-repeat"></span>
                                            <span>恢复</span>
                                        </a>
                                        <?php endif; ?>
                                        <a data-url="/admin/shift/delcom?id=<?php echo $vo['sid']; ?>&del=3" class="btn btn-danger confir">
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
        </div>
    </div>
    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/static/tpl/js/plugins/footable/footable.all.min.js"></script>
	<script type="text/javascript" src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>
    <!-- 页面js -->
    <script type="text/javascript">
       $(function () {
            // 初始话表格
            $('.footable').footable();
            // 列表展开
            $('body').on('click','tr',function(){
                var self = $(this);
                setTimeout(function(){
                    var type = self.attr("data-type");
                    if(type == 2){
                        self.next('.footable-row-detail').find('.footable-row-detail-row').eq(2).hide();
                        self.next('.footable-row-detail').find('.footable-row-detail-row').eq(3).find('.footable-row-detail-name').text('提货费');
                        self.next('.footable-row-detail').find('.footable-row-detail-row').eq(4).find('.footable-row-detail-name').text('配送费');
                    }
                },10)
            });
            // 查询
            $('#search').click(function(){
                var psel = document.getElementById("provSelect1");
                var startcity = $(".startcity option:selected").val();
                if (startcity=='A') {
                    startcity='';
                };
                var endcity = $(".endcity option:selected").val();
                if (endcity=='A') {
                    endcity='';
                };
                window.location.href='/admin/shift/index?search='+psel.value+'&startcity='+startcity+"&endcity="+endcity;
            });
            // 删除
            $('.confir').click(function(){
            	
            	var url = $(this).attr("data-url");
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
