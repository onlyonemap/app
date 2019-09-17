<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"D:\WWW\chitu_admin\public/../application/admin\view\setting\low_price.html";i:1547096419;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>

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
            <div class="col-sm-6">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>同城低价费用系数设置</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-info" aria-label="Bold">低价整车 : 同城发布参考价 </button>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control input-sm" type="number" step="10" min="0" value="<?php echo $setting['city_price_low']; ?>" placeholder="同城发布参考价"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="18">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-info" aria-label="Bold">低价整车 : 同城发布发布价低于参考价时增加费用 </button>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control input-sm" type="number" step="10" min="0" value="<?php echo $setting['city_price_reduce']; ?>" placeholder="发布价低于参考价时增加费用"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="19">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-info" aria-label="Bold">低价整车 : 同城发布发布价高于参考价时增加系数</button>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control input-sm" type="number" step="0.1" min="0" value="<?php echo $setting['city_price_add']; ?>" placeholder="高于参考价时增加百分比"  minlength="3"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="20">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
            </div>
            <!-- 城配费用系数设置 end -->
            <!-- 整车费用系数设置 start -->
            <div class="col-sm-6">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>城际低价费用系数设置</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-info" aria-label="Bold">低价整车 : 城际发布参考价 </button>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control input-sm" type="number" step="10" min="0" value="<?php echo $setting['vehicle_price_low']; ?>" placeholder="城际发布参考价"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="21">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-info" aria-label="Bold">低价整车 : 城际发布发布价低于参考价时增加费用 </button>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control input-sm" type="number" step="10" min="0" value="<?php echo $setting['vehicle_price_reduce']; ?>" placeholder="发布价低于参考价时增加费用"  minlength="2"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="22">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-info" aria-label="Bold">低价整车 : 城际发布发布价高于参考价时增加系数</button>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control input-sm" type="number" step="0.1" min="0" value="<?php echo $setting['vehicle_price_add']; ?>" placeholder="高于参考价时增加百分比"  minlength="3"/>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary pull-right update" data-index="23">修改</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
            </div>
            <!-- 整车费用系数设置 end -->
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
                    url: '/admin/setting/update',
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
                // 修改的系数类型
                var index = $(this).attr('data-index');
                // 修改的系数类型的值
                var value = $(this).parent().parent().find('input').val();
                // 提交参数
                var data = {
                    id : index,
                    updateVal: value
                };
                // 更新数据
                update(data);
            });
        });
    </script>
</body>
</html>
