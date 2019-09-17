<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:71:"D:\WWW\chitu_admin\public/../application/admin\view\driver\carlist.html";i:1566628889;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


    <link href="/static/tpl/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
    <style type="text/css">
        .lightBoxGallery{
            display: flex;
            display: -webkit-flex;
            display: -moz-flex;
            display: -o-flex;
            align-items: center;
            -webkit-align-items: center;
            -moz-align-items: center;
            height: 200px;
            text-align: center;
        }
    </style>
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content  animated fadeInRight" style="height: 100%">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>车型列表</h5>
                        <div class="ibox-tools">
                            <a class="dropdown-toggle"  href="/admin/driver/cartype.html" style="color:#000">
                                <i class="fa fa-wrench" ></i>
                                添加车型
                            </a>  
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <div class="col-sm-3">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <i class="fa fa-car"></i>
                                        <?php echo $vo['carparame']; ?>
                                    </div>
                                    <div class="panel-body">
                                        <div class="lightBoxGallery">
                                            <?php if(($vo['avatar'] !='')): ?>
                                            <a href="<?php echo $vo['avatar']; ?>" title="车型照片" data-gallery="">
                                                <img src="<?php echo $vo['avatar']; ?>" style="width: 100%;height:200px" />
                                            </a>
                                            <?php else: ?>
                                            <a href="/static/tpl/img/default.png" title="车型照片" data-gallery="">
                                                <img src="/static/tpl/img/default.png" style="width: 100%;height:200px" />
                                            </a>
                                            <?php endif; ?>
                                            <div id="blueimp-gallery" class="blueimp-gallery">
                                                <div class="slides"></div>
                                                <h3 class="title"></h3>
                                                <a class="prev"><</a>
                                                <a class="next">></a>
                                                <a class="close">×</a>
                                                <a class="play-pause"></a>
                                                <ol class="indicator"></ol>
                                            </div>
                                        </div>
                                        <p class="text-primary m-t-sm">最高承载吨位 ( t ) : <?php echo $vo['allweight']; ?></p>
                                        <p class="text-primary">最高承载立方 ( m³ ) : <?php echo $vo['allvolume']; ?></p>
                                        <p class="text-primary">车型参数 ( L*W*H ): <?php echo $vo['dimensions']; ?></p>
                                        <p class="text-primary">车速 ( km/h ): <?php echo $vo['klio']; ?></p>
                                    </div>
                                    <div class="panel-footer">
                                        <a class="pull-left" href="/admin/driver/updatecartype?id=<?php echo $vo['car_id']; ?>"><i class="fa fa-paste"></i> 编辑 </a>
                                        <a class="pull-right confir" data-ur="/admin/driver/delcar?id=<?php echo $vo['car_id']; ?>&del=1"><i class="fa fa-trash-o "></i> 删除 </a>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

   
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>

<script src="/static/tpl/js/plugins/layer/layer.min.js"></script>
 <!-- 图片画廊 -->
<script src="/static/tpl/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>
<!-- 自定义js -->
<script src="/static/tpl/js/content.js?v=1.0.0"></script>

		<script>
            //删除
            $('.confir').click(function(){
            	
            	var ur = $(this).attr("data-ur");
                layer.confirm('删除后数据将无法找回，确定要执行该操作吗？', {
                    btn: ['确定','取消'] //按钮
                }, function(){
                    window.location.href=ur;
                }, function(){
                	
                });
                
            });
		</script>
    

</body>

</html>
