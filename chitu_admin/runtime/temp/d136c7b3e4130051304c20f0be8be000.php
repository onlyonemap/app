<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:75:"D:\WWW\chitu_admin\public/../application/admin\view\setting\updatecity.html";i:1566727293;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
        <div class="col-sm-12 scaleWrop" data-type="<?php echo $res['cost_id']; ?>">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>
                        <a href="javascript:history.back(-1);"><span class="glyphicon glyphicon-chevron-left"><b>返回</b></span></a>
                    </h5>
                    <div class="ibox-tools">修改开通城市</div>
                </div>

                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-info" aria-label="Bold">城市名</button>
                        </div>
                        <div class="col-sm-4">
                            <input class="form-control input-sm" type="text" disabled value="<?php echo $res['name']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-4">

                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-info" aria-label="Bold">每小时价格</button>
                        </div>
                        <div class="col-sm-4">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['scale_hour']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="2">修改</button>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-info" aria-label="Bold">起步价系数</button>
                        </div>
                        <div class="col-sm-4">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['start_fare']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="3">修改</button>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-info" aria-label="Bold">起步价包含公里数</button>
                        </div>
                        <div class="col-sm-4">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['scale_klio']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="5">修改</button>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-info" aria-label="Bold">单公里价格系数</button>
                        </div>
                        <div class="col-sm-4">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['scale_price']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="4">修改</button>
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
         * 更新费用配置
         * @param  {Object} data 提交参数
         * @return {[type]}      [description]
         */
        function update(data) {
            // 设置loading
            var loadding = layer.load();


            $.ajax({
                url: '/admin/setting/savecity',
                type: 'POST',
                dataType: 'json',
                data: data,
            })
                .done(function(response) {
                    console.log(response);
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
        // 点击更新数据
        $('.update').click(function (argument) {
            var type = $(this).parents('.scaleWrop').attr('data-type');
            console.log(type);
            // 修改类型 1单条信息查看费用 2充值金额 3充值金额查看数量 4包年充值金额
            var index = $(this).attr('data-index');


            // 修改类型的值
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
            console.log(data);
            update(data);
        });
    });
</script>
</body>
</html>
