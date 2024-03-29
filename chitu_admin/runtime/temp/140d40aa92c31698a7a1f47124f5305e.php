<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:71:"D:\WWW\chitu_admin\public/../application/admin\view\shift\addshift.html";i:1568088216;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


    <link href="/static/tpl/css/plugins/iCheck/custom.css" rel="stylesheet" />
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">

    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5><a href="javascript:history.back();"><i class="fa fa-chevron-left"></i> 返回 </a></h5>
            <div class="ibox-tools"><a href="/admin/shift/informention"><span class="" style="font-size:18px;color: #337ab7;">填写模板示例</span></a></div>
        </div>
        <div class="ibox-content">
            <form class="form-horizontal m-t" id="commentForm" method="post" action="/admin/shift/addmessag">
                <div class="form-group">
                    <label class="col-sm-2 control-label">公司名称<span style="color:red"> *</span></label>
                    <div class="col-sm-10">
                        <input id="key" placeholder="请填选承运公司名称" name="name" minlength="2" type="text" class="form-control" required="" aria-required="true">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">班次号<span style="color:red"> *</span></label>
                    <div class="col-sm-10">
                        <input id="ShiftNumber" placeholder="请填写班次(公司前两首字母+始发城市首字母+目的城市首字母)" name="ShiftNumber" minlength="2" type="text" class="form-control" required="" aria-required="true">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group selectAddress">
                    <label class="col-sm-2 control-label">始发地<span style="color:red"> *</span> </label>
                    <div class="col-sm-3">
                        <select  class="form-control pro" name="tpro"></select>
                    </div>
                    <div class="col-sm-3">
                        <select  class="form-control city" name="tcity"></select>
                    </div>
                    <div class="col-sm-3">
                        <select  class="form-control area" name="tarea"></select>
                    </div>
                </div>

                <div class="hr-line-dashed"></div>
                <div class="form-group selectAddress">
                    <label class="col-sm-2 control-label">终点地<span style="color:red"> *</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control pro" name="ppro"></select>
                    </div>
                    <div class="col-sm-3">
                        <select  class="form-control city" name="pcity"></select>
                    </div>
                    <div class="col-sm-3">
                        <select  class="form-control area" name="parea"></select>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">线路信息<span style="color:red"> *</span> </label>
                    <div class="col-sm-10">
                        <div class="radio i-checks">
                            <label><input type="radio" value="1"  checked="" name="istransit"><i></i>直达(选中)</label>
                        </div>
                        <div class="radio i-checks">
                            <label><input type="radio"  value="2"   name="istransit"><i></i>中转</label>
                        </div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group selectAddress">
                    <label class="col-sm-2 control-label">中转站点</label>
                    <div class="col-sm-5">
                        <select  class="form-control pro" name="tpro"></select>
                    </div>
                    <div class="col-sm-5">
                        <select  class="form-control city" name="transit"></select>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group selectAddress">
                    <label class="col-sm-2 control-label">始发仓地址：<span style="color:red"> *</span></label>
                    <div class="col-sm-10">
                        <div class="row">
                            <div class="col-sm-3">
                                <select class="form-control pro" name="sheng"  required="" aria-required="true"></select>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control city" name="shi" required="" aria-required="true"></select>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control area" name="xian" required="" aria-required="true"></select>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="beginAddress" required="" aria-required="true" placeholder="请填详细写地址" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">仓库收货时间段<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="" name="stime"  placeholder="请填写时间段格式：10:00-18:00">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">始发仓联系人<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="" name="sphone"  placeholder="请填写联系人电话">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group selectAddress">
                    <label class="col-sm-2 control-label">终点仓地址：<span style="color:red"> *</span></label>
                    <div class="col-sm-10">
                        <div class="row">
                            <div class="col-sm-3">
                                <select class="form-control pro" name="sheng1" required="" aria-required="true"></select>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control city" name="shi1" required="" aria-required="true"></select>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control area"  name="xian1" required="" aria-required="true"></select>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" placeholder="请填详细写地址"  name="endAddress" required="" aria-required="true" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">仓库提货时间段<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="" name="dtime"  placeholder="请填写时间段格式：10:00-18:00">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">终点仓联系人<span style="color:red"> *</span></label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="" name="tphone"  placeholder="请填写联系人电话">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">免费提货重量限制(kg)</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" id="FreeTonnage" name="FreeTonnage"  placeholder="请填写免费提货最小重量要求(kg/提货点)">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">干线最低收费</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" id="lowprice" name="lowprice" placeholder="干线班次最低收费价格">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">添加</label>
                    <div class="col-sm-10 col-xs-12" ><a id="AddMoreFileBox"  class="btn btn-info">添加更多的干线价格费用率约束条件</a></div>
                </div>

                <div class="form-group" id="InputsWrapper">
                    <div class="zhongliangtext" style="height: 45px;">
                        <label class="col-sm-2 control-label">重量范围：</label>
                        <div class="col-sm-10" >
                            <i class="col-sm-2 col-lg-3 "><input type="text" class="inputos form-control row" name="mytext1[]" id="field1_1" /></i>
                            <p class="col-sm-2 col-lg-1  form-control-static">KG 到</p>
                            <i class="col-sm-2 col-lg-3 "><input type="text" class="inputos form-control" name="mytext2[]" id="field2_1"/></i>
                            <p class="col-sm-1 col-lg-1 form-control-static">KG</p>
                            <p class="col-sm-2 col-lg-1  form-control-static">价格</p>
                            <i class="col-sm-2 col-lg-2 "><input type="text" class="inputos form-control" name="mytext3[]" id="field3_1" placeholder="请填写￥/KG" /></i>
                            <a href="#" class="removeclass col-sm-1 col-lg-1  form-control-static">×</a>
                        </div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <div class="col-sm-6 col-xs-12 telescopic">
                        <label class="col-sm-4 control-label">发车窗口(每周)<span style="color:red"> *</span></label>
                        <div class="col-sm-8">
                            <input type="checkbox" class="i-checks datatime "  name="datatime[]" value="周一"></span>周一
                            <span style="padding-left: 10px"><input type="checkbox" class="i-checks datatime "  name="datatime[]" value="周二"></span>周二
                            <span style="padding-left: 10px"><input type="checkbox" class="i-checks datatime "  name="datatime[]" value="周三"></span>周三
                            <span style="padding-left: 10px"><input type="checkbox" class="i-checks datatime "  name="datatime[]" value="周四"></span>周四
                            <span style="padding-left: 10px"><input type="checkbox" class="i-checks datatime "  name="datatime[]" value="周五"></span>周五
                            <span style="padding-left: 10px"><input type="checkbox" class="i-checks datatime "  name="datatime[]" value="周六"></span>周六
                           <span style="padding-left: 10px"> <input type="checkbox" class="i-checks datatime "  name="datatime[]" value="周日"></span>周日

<!--                            <select class="form-control m-b" style="height:40px;" id="DeWin" name="DeWin" >-->
<!--                                <?php if(is_array($arr) || $arr instanceof \think\Collection || $arr instanceof \think\Paginator): if( count($arr)==0 ) : echo "" ;else: foreach($arr as $key=>$vo): ?>-->
<!--                                <option><?php echo $vo; ?> </option>-->
<!--                                <?php endforeach; endif; else: echo "" ;endif; ?>-->
<!--                            </select>-->
                        </div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <div class="col-sm-6 col-xs-12 telescopic">
                        <label class="col-sm-4 control-label">发车时段<span style="color:red"> *</span></label>
                        <div class="col-sm-8">
                            <div class="col-sm-5 row">
                                <select class="form-control m-b" id="TimeStrat" name="TimeStrat"  style="max-height:50px; overflow-y:auto">
                                    <?php
                                $tim = array(
                                       "00:00" => "00:00","01:00" => "01:00","02:00" => "02:00","03:00" => "03:00","04:00" => "04:00","05:00" => "05:00","06:00" => "06:00","07:00" => "07:00",
                                    "08:00" => "08:00","09:00" => "09:00","10:00" => "10:00","11:00" => "11:00","12:00" => "12:00","13:00" => "13:00","14:00" => "14:00","15:00" => "15:00",
                                    "16:00" => "16:00","17:00" => "17:00","18:00" => "18:00","19:00" => "19:00","20:00" => "20:00","21:00" => "21:00","22:00" => "22:00","23:00" => "23:00"
                                    );
                                    foreach($tim as $key => $value) { ?>
                                    <option><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <i class="col-sm-2  fa fa-arrows-h" style="text-align:center; font-size:20px; line-height:34px;"></i>
                            <div class="col-sm-5" row>
                                <select class="form-control m-b" id="TimeEnd" name="TimeEnd"  style="max-height:50px; overflow-y:auto">
                                    <?php
                                  foreach($tim as $key => $value) { ?>
                                    <option><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 telescopic">
                        <label class="col-sm-4 control-label">自行送货截止时间(提前量)：</label>
                        <div class="col-sm-8">
                            <select class="form-control m-b"  id="SelfDeliveryDeadline" name="SelfDeliveryDeadline"  style="max-height:50px; overflow-y:auto">
                                <?php
                              $hou = array(
                                     "1h" => "1h","2h" => "2h","3h" => "3h","4h" => "4h","5h" => "5h","6h" => "6h","7h" => "7h","8h" => "8h",
                                "9h" => "9h","10h" => "10h","11h" => "11h","12h" => "12h","13h" => "13h","14h" => "14h","15h" => "15h","16h" => "16h",
                                "17h" => "17h","18h" => "18h","19h" => "19h","20h" => "20h","21h" => "21h","22h" => "22h","23h" => "23h"
                                );
                                foreach($hou as $key => $value) { ?>
                                <option><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">时效（D）：</label>
                    <div class="col-sm-4">
                        <select class="form-control" name="TrunkAging">
                            <?php
                        $day = array("1天" => "1天","2天" => "2天","3天" => "3天","4天" => "4天","5天" => "5天","6天" => "6天","7天" => "7天",
                            "8天" => "8天","9天" => "9天","10天" => "10天","11天" => "11天","12天" => "12天","13天" => "13天","14天" => "14天","15天" => "15天");
                            foreach($day as $key => $value) { ?>
                            <option><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>

                <div class="form-group">
                <div class="col-sm-6 col-xs-12 telescopic">
                    <label class="col-sm-4 control-label">到车时段：<span style="color:red"> *</span></label>
                    <div class="col-sm-8">
                        <div class="col-sm-5 col-xs-5 row">
                            <select class="form-control m-b" id="ArriveTimeStart" name="ArriveTimeStart"  style="max-height:50px; overflow-y:auto">
                                <?php
                                  foreach($tim as $key => $value) { ?>
                                <option><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <i class="col-sm-2 col-xs-2 fa fa-arrows-h" style="text-align:center; font-size:20px; line-height:34px;"></i>
                        <div class="col-sm-5 col-xs-5">
                            <select class="form-control m-b" id="ArriveTimeEnd" name="ArriveTimeEnd" style="max-height:50px; overflow-y:auto">
                                <?php
                                 foreach($tim as $key => $value) { ?>
                                <option><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

<!--                <div class="hr-line-dashed"></div>-->
                <div class="form-group">
                    <div class="col-sm-6 telescopic">
                        <label class="col-sm-4 control-label">自行提货最早时间(延迟量)：<span style="color:red"> *</span></label>
                        <div class="col-sm-8">
                            <select class="form-control m-b" id="MorningTime" name="MorningTime"  style="max-height:50px; overflow-y:auto">
                                <?php
                             foreach($hou as $key => $value) { ?>
                                <option><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-6 telescopic">
                        <label class="col-sm-4 control-label">折扣：<span style="color:red"> *</span></label>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" name="discount">
                        </div>
                    </div>
                </div>



                <div class="form-group">
                    <label class="col-sm-2 control-label">是否开启 </label>
                    <div class="col-sm-10">
                        <div class="radio i-checks">
                            <label><input type="radio" value="2" name="whethertoopen"><i></i>关</label>
                        </div>
                        <div class="radio i-checks">
                            <label><input type="radio"  value="1"  checked="" name="whethertoopen"><i></i> 开（选中）</label>
                        </div>
                    </div>
                </div>



                <div class="hr-line-dashed"></div>

                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <input type="hidden" name="action" value="add">
                        <button class="btn btn-primary" type="submit">保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>



<!-- 全局js -->
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<!-- bootstrap -->
<script src="/static/tpl/js/bootstrap.min.js?v=3.3.6"></script>
<!-- jQuery Validation plugin javascript-->


<script src="/static/tpl/js/shiftPage.js"></script>
<script language="javascript" src="/static/tpl/ui/form.js"></script>
<link rel="stylesheet" href="/static/tpl/ui/jquery.ui.autocomplete.css">
<script type="text/javascript" src="/static/tpl/ui/jquery.min.js"></script>
<script type="text/javascript" src="/static/tpl/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="/static/tpl/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="/static/tpl/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="/static/tpl/ui/jquery.ui.autocomplete.js"></script>
<!-- jQuery Validation plugin javascript-->
<script src="/static/tpl/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/static/tpl/js/plugins/validate/messages_zh.min.js"></script>
<!-- iCheck -->
<script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>
<!-- 地址三级联动 -->
<script src="/static/tpl/js/common/select-address.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // radio 初始化
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
        // 公司自动补全
        $( "#key" ).autocomplete({
            source: "/admin/common/search",
            minLength: 2,
            autoFocus: true
        });

        // 表单验证
        $("#commentForm").validate({
            errorElement : 'span',
            errorClass : 'help-block',
            rules : {
                ShiftNumber : {
                    required : true,
                    remote : {
                        type:"post",
                        url:"/admin/shift/checknumber",
                        data:{
                            name:function(){
                                return $("#ShiftNumber").val();
                            }
                        }
                    }
                }
            },
            messages : {
                ShiftNumber : {
                    required : "请输入班次号",
                    remote :  "班次号已存在!!!" ,
                },
            },
            //自定义错误消息放到哪里
            errorPlacement : function(error, element) {
                element.next().remove();//删除显示图标
                element.after('<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>');
                element.after(error);//显示错误消息提示
            },
            //给未通过验证的元素进行处理
            highlight : function(element) {
                $(element).closest(".form-group").removeClass("has-success has-feedback").addClass("has-error has-feedback")
            },
            //验证通过的处理
            success : function(label) {
                var el=label.closest('.form-group')
                el.removeClass('has-error has-feedback').addClass("has-success has-feedback");
                el.find('.has-error').remove();
                el.find('.glyphicon-remove').remove();
                var el=label.closest('.form-group').find("input");
                label.remove();
            },
        });
    });
</script>


</body>

</html>
