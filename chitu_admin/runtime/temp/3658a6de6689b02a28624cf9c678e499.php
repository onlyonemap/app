<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:75:"D:\WWW\chitu_admin\public/../application/admin\view\setting\scalePrice.html";i:1566572332;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>

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


    <style type="text/css">.btn{width: 100%;}</style>
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <!-- 城配费用系数设置 start -->
            <div class="col-sm-6 scaleWrop" data-type="1">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>城配费用系数设置</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">起步价系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_startprice']; ?>" placeholder="起步价系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="1">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">里程偏离系数(0-100)</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_km']; ?>" placeholder="里程偏离系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="2">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">里程偏离系数(100-300)</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_km_two']; ?>" placeholder="里程偏离系数(0-100)"  minlength="3"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="3">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">里程偏离系数(300-1000)</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_km_three']; ?>" placeholder="里程偏离系数(100-300)"  minlength="3"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="4">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">里程偏离系数(1000以上)</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_km_four']; ?>" placeholder="里程偏离系数(300-1000)"  minlength="3"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="5">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">单公里价格系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_price_km']; ?>" placeholder="单公里价格系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="6">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">装货费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_pickgood']; ?>" placeholder="装货费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="7">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">卸货费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_sendgood']; ?>" placeholder="卸货费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="8">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">多点提配系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_multistore']; ?>" placeholder="多点提配系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="9">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">当日配送费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_sameday']; ?>" placeholder="当日配送费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="10">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">次日配送费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_seconday']; ?>" placeholder="次日配送费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="11">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">两天后提配费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceCity['scale_moreday']; ?>" placeholder="两天后提配费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="12">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
            </div>
            <!-- 城配费用系数设置 end -->
            <!-- 整车费用系数设置 start -->
            <div class="col-sm-6 scaleWrop" data-type="2">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>整车费用系数设置</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">起步价系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_startprice']; ?>" placeholder="起步价系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="1">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">里程偏离系数(0-100)</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_km']; ?>" placeholder="里程偏离系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="2">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">里程偏离系数(100-300)</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_km_two']; ?>" placeholder="里程偏离系数(0-100)"  minlength="3"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="3">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">里程偏离系数(300-1000)</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_km_three']; ?>" placeholder="里程偏离系数(100-300)"  minlength="3"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="4">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">里程偏离系数(1000以上)</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_km_four']; ?>" placeholder="里程偏离系数(300-1000)"  minlength="3"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="5">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">单公里价格系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_price_km']; ?>" placeholder="单公里价格系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="6">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">装货费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_pickgood']; ?>" placeholder="装货费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="7">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">卸货费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_sendgood']; ?>" placeholder="卸货费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="8">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">多点提配系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_multistore']; ?>" placeholder="多点提配系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="9">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">当日配送费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_sameday']; ?>" placeholder="当日配送费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="10">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">次日配送费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_seconday']; ?>" placeholder="次日配送费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="11">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">两天后提配费用系数</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['scale_moreday']; ?>" placeholder="两天后提配费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="12">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">返程计费标准</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="0.1" max="2" min="0" value="<?php echo $priceVehicle['goback']; ?>" placeholder="两天后提配费用系数"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="14">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                    </div>
                </div>
            </div>
            <!-- 整车费用系数设置 end -->
            <!-- 城配促销优惠折扣系数设置 start -->
            <div class="col-sm-6 scaleWrop" data-type="1">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>城配优惠折扣系数设置</h5>
                    </div>
                    <div class="ibox-content">
                       <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">优惠折扣</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="1" max="2" min="0" value="<?php echo $priceCity['scale_discount']; ?>" placeholder="促销优惠折扣"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="13">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
            </div>
            <!-- 城配促销优惠折扣系数设置 end -->
            <!-- 整车促销优惠折扣系数设置 start -->
            <div class="col-sm-6 scaleWrop" data-type="2">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>整车优惠折扣系数设置</h5>
                    </div>
                    <div class="ibox-content">
                       <div class="row">
                            <div class="col-sm-4">
                                <button type="button" class="btn btn-info" aria-label="Bold">优惠折扣</button>
                            </div>
                            <div class="col-sm-6">
                                <input class="form-control input-sm" type="number" step="1" max="2" min="0" value="<?php echo $priceVehicle['scale_discount']; ?>" placeholder="促销优惠折扣"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="13">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
            </div>
            <!-- 整车促销优惠折扣系数设置 end -->
        </div>
    </div>
    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/static/tpl/js/plugins/layer/layer.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            /**
             * 更新费用配置系数
             * @param  {Object} data 提交参数
             * @return {[type]}      [description]
             */
            function update(data) {
                // 设置loading
                var loadding = layer.load();

                $.ajax({
                    url: '/admin/setting/updateScalePrice',
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
            // 点击更新系数
            $('.update').click(function (argument) {
                var type = $(this).parents('.scaleWrop').attr('data-type');
                // 修改的系数类型 1起步价系数 2里程偏离系数 3单公里价格系数 4装货费用系数 5卸货费用系数 6多点提配系数 7返程计费标准
                var index = $(this).attr('data-index');
                // 允许的最小值
                var min = $(this).parent().parent().find('input').attr('min');
                // 允许的最大值
                var max = $(this).parent().parent().find('input').attr('max');
                // 修改的系数类型的值
                var value = $(this).parent().parent().find('input').val();
                // 提交参数
                var data = {
                    type : type,
                    scaleType:index,
                    updateVal: value
                };
                // 如果小于最小值或者大于最大值
                if(value < 0) return false;
                // 更新数据
                update(data);
            });
        });
    </script>
</body>
</html>
