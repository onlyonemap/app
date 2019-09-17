<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:77:"D:\WWW\chitu_admin\public/../application/admin\view\driver\updatecartype.html";i:1566629427;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5><a href="javascript:history.back();"><i class="fa fa-chevron-left"></i> 返回 </a></h5>
                        <div class="ibox-tools">修改车型</div>
                    </div>
                    <div class="ibox-content">
                        <form class="form-horizontal m-t" enctype="multipart/form-data" method="post" action="/admin/driver/addcartype" id="commentForm">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">车型名称:<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="carparame" placeholder="请输入车型" name="carparame"  type="text" class="form-control" value="<?php echo $list['carparame']; ?>" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">车公里单价(元/公里):<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="costkm" placeholder="车/公里(元/公里)" name="costkm"  type="text" class="form-control" required="" value="<?php echo $list['costkm']; ?>" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">公里数（公里/时）:<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="klio" placeholder="车/公里(元/公里)" name="klio"  type="text" class="form-control" required="" value="<?php echo $list['klio']; ?>" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">起步价格:<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="lowprice" placeholder="最低价格(元)" name="lowprice"  type="text" class="form-control" required="" value="<?php echo $list['lowprice']; ?>" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">包车价格(8h/150km):<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="chartered" value="<?php echo $list['chartered']; ?>" placeholder="包车价格(元)" name="chartered"  type="text" class="form-control" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">司机装货费用:<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="pickup" value="<?php echo $list['pickup']; ?>" placeholder="司机装货费用(元)" name="pickup"  type="text" class="form-control" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">司机卸货费用:<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="unload" value="<?php echo $list['unload']; ?>" placeholder="司机卸货费用(元)" name="unload"  type="text" class="form-control" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">多点提配增价:<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="morepickup" placeholder="多点提配增价(元/点)" name="morepickup"  type="text" class="form-control" value="<?php echo $list['morepickup']; ?>" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">最高承载吨位:<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="allweight" placeholder="请输入最高承载吨位" name="allweight"  type="text" class="form-control" value="<?php echo $list['allweight']; ?>" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">最高承载立方:<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="allvolume" value="<?php echo $list['allvolume']; ?>" placeholder="请输入最高承载立方" name="allvolume" maxlength="11" type="text" class="form-control" required="" >
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">车型参数:</label>
                                <div class="col-sm-10">
                                   <input id="dimensions" value="<?php echo $list['dimensions']; ?>" name="dimensions" maxlength="11" type="text" class="form-control" placeholder="请输入车型参数长*宽*高（L*W*H）" >
                                </div>
                                
                            </div>
                           <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">信息图片:</label>
                                <div class="col-sm-10">
                                   <img src="__ROOT__<?php echo $list['avatar']; ?>" width="200px" hieght="200px;" />
                                </div>
                            </div>
                           <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">车型图片:</label>
                                <div class="col-sm-10">
                                   <input type="file" name="image" />
                                </div>
                            </div>
                           
                           <div class="hr-line-dashed"></div>
                           
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <input type="hidden" value="update" name="action" />
                                    <input type="hidden" value="<?php echo $list['car_id']; ?>" name="id" />
                                    <button class="btn btn-primary" type="submit">保存内容</button>
                                    <a class="btn btn-danger" onClick="javascript :history.back(-1);" style="width:82px;" >取消</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

  

    <!-- 全局js -->
    <script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
    <script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>

    <!-- 自定义js -->
    <script src="/static/tpl/js/content.js?v=1.0.0"></script>
    <!-- jQuery Validation plugin javascript-->
    <script src="/static/tpl/js/plugins/validate/jquery.validate.min.js"></script>
    <script src="/static/tpl/js/plugins/validate/messages_zh.min.js"></script>
    <!-- iCheck -->
    <script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });
    </script>

    
    

</body>

</html>
