<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:69:"D:\WWW\chitu_admin\public/../application/admin\view\setting\city.html";i:1566727277;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
                    <h5>市内开通城市</h5>
                    <div class="ibox-tools">
                        <a class="dropdown-toggle"  href="/admin/setting/addcity" style="color: #000;">
                            <span class="glyphicon glyphicon-plus"></span>
                            <span>添加</span>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">


                    <table class="footable table table-stripped toggle-arrow-tiny">
                        <thead>
                        <tr>
                            <th data-toggle="true" class="text-left">ID</th>
                            <th class="text-left">开通城市名称</th>
                            <th class="text-left">每小时价格</th>
                            <th class="text-left">起步价系数</th>
                            <th class="text-left">起步价包含公里数</th>
                            <th class="text-left">单公里价格系数</th>

                            <th class="text-left">状态</th>
                            <th class="text-left">添加时间</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr data-type="">
                            <td class="text-left"><?php echo $vo['cost_id']; ?></td>
                            <td class="text-left"><?php echo $vo['name']; ?></td>
                            <td class="text-left"><?php echo $vo['scale_hour']; ?></td>
                            <td class="text-left"><?php echo $vo['start_fare']; ?></td>
                            <td class="text-left"><?php echo $vo['scale_klio']; ?></td>
                            <td class="text-left"><?php echo $vo['scale_price']; ?></td>
                            <td class="text-left">
                                <?php if(($vo['delstate'] ==1)): ?>
                                开启
                                <?php else: ?>
                                关闭
                                <?php endif; ?>
                            </td>
                            <td class="text-left"><?php echo $vo['addtime']; ?></td>
                            <td class="text-center">
                                <a href="/admin/setting/todetail?id=<?php echo $vo['cost_id']; ?>" class="btn btn-info">
                                    <span class="glyphicon glyphicon-file"></span>
                                    <span>详情</span>
                                </a>
                                <a href="/admin/setting/updatecity?id=<?php echo $vo['cost_id']; ?>" class="btn btn-warning">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                    <span>修改</span>
                                </a>
                                <a data-url="/admin/setting/delcom?id=<?php echo $vo['cost_id']; ?>&del=2" class="btn btn-danger confir">
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
