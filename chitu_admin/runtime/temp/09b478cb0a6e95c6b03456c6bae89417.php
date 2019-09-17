<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:76:"D:\WWW\chitu_admin\public/../application/admin\view\setting\information.html";i:1558515984;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
        <div class="col-sm-6 scaleWrop" data-type="1">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>承运端信息发布设置</h5>
                </div>

                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">单条信息查看费用</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['viewprice']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="1">修改</button>
                        </div>
                    </div>

                <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">充值金额</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['vipprice']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="2">修改</button>
                        </div>
                    </div>

                <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">充值金额查看数量</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['vipcount']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="3">修改</button>
                        </div>
                    </div>

                <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">包年充值金额</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['yearly']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="4">修改</button>
                        </div>
                    </div>

                <div class="hr-line-dashed"></div>
                </div>
                <div class="ibox-title">
                    <h5>用户端整车信息发布设置</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">客户保证金门槛</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['deposit']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="5">修改</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">发布单条信息费用</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['charge']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="6">修改</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">违规取消订单扣费</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $res['cancleprice']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="7">修改</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>

                </div>
            </div>
        </div>
        <!-- 整车费用系数设置 end -->

        <!-- 城配费用信息设置start -->
        <div class="col-sm-6 scaleWrop" data-type="2">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>承运端信息发布设置</h5>
                </div>

                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">单条信息查看费用</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $data['viewprice']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="1">修改</button>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">充值金额</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $data['vipprice']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="2">修改</button>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">充值金额查看数量</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $data['vipcount']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="3">修改</button>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">包年充值金额</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $data['yearly']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="4">修改</button>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>
                </div>
                <div class="ibox-title">
                    <h5>用户端城配信息发布设置</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">客户保证金门槛</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $data['deposit']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="5">修改</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">发布单条信息费用</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $data['charge']; ?>" placeholder="" />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="6">修改</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-info" aria-label="Bold">违规取消订单扣费</button>
                        </div>
                        <div class="col-sm-6">
                            <input class="form-control input-sm" type="number"  value="<?php echo $data['cancleprice']; ?>" placeholder=""  />
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary pull-right update" data-index="7">修改</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>

                </div>
            </div>
        </div>
        <!-- 城配费用信息设置start -->
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
                url: '/admin/setting/updatePrice',
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
            console.log(123);
            var type = $(this).parents('.scaleWrop').attr('data-type');
            // 修改类型 1单条信息查看费用 2充值金额 3充值金额查看数量 4包年充值金额 5客户保证金门槛 6发布单条信息费用 7 违规取消订单扣费
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
