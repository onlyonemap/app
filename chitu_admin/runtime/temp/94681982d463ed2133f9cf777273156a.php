<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"D:\WWW\chitu_admin\public/../application/admin\view\setting\promotion.html";i:1531107735;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>

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


    <link href="/static/tpl/css/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="/static/tpl/css/plugins/bootstrap-switch/bootstrap-switch.css">
    <style type="text/css">.btn{width: 100%;}</style>
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <!-- 城配促销设置 start -->
            <div class="col-sm-6 scaleWrop" data-type="1">

            	<div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>城配促销活动设置</h5>
                    </div>
                </div>
				<!-- 促销日前十单优惠折扣 -->
                <div class="ibox float-e-margins promotion" data-index="1">
                    <div class="ibox-title">
                        <h5>促销日前十单优惠折扣</h5>
                    </div>
                    <div class="ibox-content">
                    	<div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开始时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control startTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['tenOrder']['startTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">结束时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control endTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['tenOrder']['endTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">折扣系数</button>
                            </div>
                            <div class="col-sm-9">
								<input class="form-control scale" type="text" value="<?php echo $priceCity['tenOrder']['scale']; ?>" />
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开关</button>
                            </div>
                            <div class="col-sm-9">
								<div class="switch" data-on="success" data-off="danger">
								    <?php if(($priceCity['tenOrder']['switch']=='1')): ?>
                                    <input type="checkbox" class="switch" checked />
                                    <?php else: ?>
                                    <input type="checkbox" class="switch" />
                                    <?php endif; ?>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary pull-right update">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
				<!-- 促销日整点订单优惠折扣 -->
                <div class="ibox float-e-margins promotion" data-index="2">
                    <div class="ibox-title">
                        <h5>促销日整点订单优惠折扣</h5>
                    </div>

                    <div class="ibox-content">
                    	<div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开始时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control startTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['wholeOrder']['startTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">结束时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control endTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['wholeOrder']['endTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">折扣系数</button>
                            </div>
                            <div class="col-sm-9">
								<input class="form-control scale" type="text" value="<?php echo $priceCity['wholeOrder']['scale']; ?>" />
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开关</button>
                            </div>
                            <div class="col-sm-9">
								<div class="switch" data-on="success" data-off="danger">
                                    <?php if(($priceCity['wholeOrder']['switch']=='1')): ?>
                                    <input type="checkbox" class="switch" checked />
                                    <?php else: ?>
                                    <input type="checkbox" class="switch" />
                                    <?php endif; ?>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary pull-right update">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
                <!-- 促销日订单号后两位相同的订单优惠 -->
                <div class="ibox float-e-margins promotion" data-index="3">
                    <div class="ibox-title">
                        <h5>促销日订单号后两位相同的订单优惠</h5>
                    </div>

                    <div class="ibox-content">
                    	<div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开始时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control startTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['twoSameorder']['startTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">结束时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control endTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['twoSameorder']['endTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">折扣系数</button>
                            </div>
                            <div class="col-sm-9">
								<input class="form-control scale" type="text" value="<?php echo $priceCity['twoSameorder']['scale']; ?>" />
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开关</button>
                            </div>
                            <div class="col-sm-9">
								<div class="switch" data-on="success" data-off="danger">
                                    <?php if(($priceCity['twoSameorder']['switch']=='1')): ?>
                                    <input type="checkbox" class="switch" checked />
                                    <?php else: ?>
                                    <input type="checkbox" class="switch" />
                                    <?php endif; ?>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary pull-right update">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
                <!-- 促销日订单号后三位相同的订单优惠折扣 -->
                <div class="ibox float-e-margins promotion" data-index="4">
                    <div class="ibox-title">
                        <h5>促销日订单号后三位相同的订单优惠折扣</h5>
                    </div>

                    <div class="ibox-content">
                    	<div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开始时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control startTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['threeSameorder']['startTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">结束时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control endTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['threeSameorder']['endTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">折扣系数</button>
                            </div>
                            <div class="col-sm-9">
								<input class="form-control scale" type="text" value="<?php echo $priceCity['threeSameorder']['scale']; ?>" />
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开关</button>
                            </div>
                            <div class="col-sm-9">
								<div class="switch" data-on="success" data-off="danger">
                                    <?php if(($priceCity['threeSameorder']['switch']=='1')): ?>
                                    <input type="checkbox" class="switch" checked />
                                    <?php else: ?>
                                    <input type="checkbox" class="switch" />
                                    <?php endif; ?>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary pull-right update">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
                <!-- 促销日订单后两位等于当天日期的优惠折扣 -->
                <div class="ibox float-e-margins promotion" data-index="5">
                    <div class="ibox-title">
                        <h5>促销日订单后两位等于当天日期的优惠折扣</h5>
                    </div>

                    <div class="ibox-content">
                    	<div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开始时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control startTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['dateOrder']['startTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">结束时间</button>
                            </div>
                            <div class="col-sm-9">
                            	<div class="input-group date form_datetime">
								    <input class="form-control endTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['dateOrder']['endTime']); ?>" readonly>
								    <span class="add-on"><i class="icon-th"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">折扣系数</button>
                            </div>
                            <div class="col-sm-9">
								<input class="form-control scale" type="text" value="<?php echo $priceCity['dateOrder']['scale']; ?>" />
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开关</button>
                            </div>
                            <div class="col-sm-9">
								<div class="switch" data-on="success" data-off="danger">
                                    <?php if(($priceCity['dateOrder']['switch']=='1')): ?>
                                    <input type="checkbox" class="switch" checked />
                                    <?php else: ?>
                                    <input type="checkbox" class="switch" />
                                    <?php endif; ?>
								</div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary pull-right update">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
                <!-- 第一次下单优惠折扣 -->
                <div class="ibox float-e-margins promotion" data-index="6">
                    <div class="ibox-title">
                        <h5>第一次下单优惠折扣</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开始时间</button>
                            </div>
                            <div class="col-sm-9">
                                <div class="input-group date form_datetime">
                                    <input class="form-control startTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['firstOrder']['startTime']); ?>" readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">结束时间</button>
                            </div>
                            <div class="col-sm-9">
                                <div class="input-group date form_datetime">
                                    <input class="form-control endTime" type="text" value="<?php echo date('Y-m-d H:i',$priceCity['firstOrder']['endTime']); ?>" readonly>
                                    <span class="add-on"><i class="icon-th"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">折扣系数</button>
                            </div>
                            <div class="col-sm-9">
                                <input class="form-control scale" type="text" value="<?php echo $priceCity['firstOrder']['scale']; ?>" />
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-info" aria-label="Bold">开关</button>
                            </div>
                            <div class="col-sm-9">
                                <div class="switch" data-on="success" data-off="danger">
                                    <?php if(($priceCity['firstOrder']['switch']=='1')): ?>
                                    <input type="checkbox" class="switch" checked />
                                    <?php else: ?>
                                    <input type="checkbox" class="switch" />
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary pull-right update">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
            </div>
            <!-- 城配促销设置 end -->
            <!-- 整车促销设置 start -->
            
            <!-- 整车促销设置 end -->
            
        </div>
    </div>
    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <script src="/static/tpl/js/plugins/bootstrap-switch/bootstrap-switch.js"></script>
    <!-- 日期插件 -->
    <script type="text/javascript" src="/static/tpl/js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
    <!-- 调用日期插件 -->
    <!-- <script type="text/javascript" src="/static/tpl/js/callDatepicker.js"></script> -->
    <script type="text/javascript">
        $(document).ready(function () {
        	$(".form_datetime").datetimepicker({
		        format: "yyyy-mm-dd hh:ii:ss",
		        startDate: new Date(),
		        autoclose: true
		    });
            /**
             * 更新费用配置系数
             * @param  {Object} data 提交参数
             * @return {[type]}      [description]
             */
            function update(data) {
                // 设置loading
                var loadding = layer.load();

                $.ajax({
                    url: '/admin/setting/updatePromotion',
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                })
                .done(function(response) {
                    if(response){
                        layer.msg('设置成功'); 
                    }else{
                        layer.msg('设置失败'); 
                    }
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    // 关闭loading
                    layer.close(loadding);
                });
            }
            // 促销开关控制
            $('.switch').on('switch-change', function (e, data) {
		        var $el = $(data.el);
            	var type = $el.parents('.scaleWrop').attr('data-type');
		        var index = $el.attr('data-index');
		        var value = data.value;
		    });  
		    // 更新促销折扣系数
            $('.update').click(function (argument) {
            	// 优惠项目 1 城配 2 整车
                var type = $(this).parents('.scaleWrop').attr('data-type');
                // 修改的类型
                var index = $(this).parents('.promotion').attr('data-index');
                // 开始时间
                var startTime = $(this).parents('.promotion').find('.startTime').val();
                // 结束时间
                var endTime = $(this).parents('.promotion').find('.endTime').val();
                // 系数
                var scale = $(this).parents('.promotion').find('.scale').val();
                // 开关
                var switchtab = $(this).parents('.promotion').find('.switch').is(':checked');
                if(switchtab){
                	switchtab = 1;
                }else{
                	switchtab = 2;
                }
                // 提交参数
                var data = {
                    type : type,
                    index:index,
                    startTime: startTime,
                    endTime: endTime,
                    scale: scale,
                    switchtab: switchtab
                };
                // 如果小于最小值或者大于最大值
                if(data.scale < 0) return false;
                // 更新数据
                update(data);
            });
        });
    </script>
</body>
</html>
