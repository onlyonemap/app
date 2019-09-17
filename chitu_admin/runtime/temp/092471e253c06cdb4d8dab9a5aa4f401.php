<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:70:"D:\WWW\chitu_admin\public/../application/admin\view\setting\index.html";i:1546485218;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
<body class="gray-bg animated fadeInRight">
    <div class="wrapper wrapper-content animated fadeInRight">
        
        <!-- app版本配置 start -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>app版本配置</h5>
            </div>

            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">用户版：IOS（1整包升级，2差量升级，3不升级）</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['update_userios_config']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="5">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">用户版：苹果版本号</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="text" value="<?php echo $setting['update_userios_version']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="7"> 修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">用户版：Android（1整包升级，2差量升级，3不升级）</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['update_userand_config']; ?>"  />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="6">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">用户版：安卓版本号</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="text" value="<?php echo $setting['update_userand_version']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="16"> 修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">承运端：IOS（1整包升级，2差量升级，3不升级）</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['update_driverios_config']; ?>"  />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="9">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">承运端：苹果版本号</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="text" value="<?php echo $setting['update_driverios_version']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="11">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">承运端：Android（1整包升级，2差量升级，3不升级） </button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['update_driverand_config']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="10">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">承运端：安卓版本号</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="text" value="<?php echo $setting['update_driverand_version']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="17">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
            </div>
        </div>
        <!-- app版本配置 end -->

        <!-- 司机接单金额限制 start -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>司机接单金额限制</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">司机接单金额限制</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['driver_robbing']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="12">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
            </div>
        </div>
        <!-- 司机接单金额限制 end -->

        <!-- 信息发布收取费用设置 start -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>信息发布收取费用设置</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">信息发布收取费用</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['Infor_delivery']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="1">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
            </div>
        </div>
        <!-- 信息发布收取费用设置 end -->

        <!-- 信息发布收取费用设置 start -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>积分换算设置</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">积分换算设置</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['integral_setting']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="13">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
            </div>
        </div>
        <!-- 信息发布收取费用设置 end -->

        <!-- 承运端：运费提现费率 start -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>承运端：运费提现费率</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">承运端：运费提现费率</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['driver_freight_rate']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="14">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
            </div>
        </div>
        <!-- 承运端：运费提现费率 end -->
        
        <!-- 承运端：余额提现费率 start -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>承运端：余额提现费率</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-info">承运端：余额提现费率</button>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control input-sm" type="number" value="<?php echo $setting['driver_balance_rate']; ?>" />
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary pull-right update" data-index="15">修改</button>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
            </div>
        </div>
        <!-- 承运端：余额提现费率 end -->
        
    </div>
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
