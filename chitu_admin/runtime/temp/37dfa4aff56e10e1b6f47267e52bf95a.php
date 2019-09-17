<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"D:\WWW\chitu_admin\public/../application/admin\view\shift\updateshift.html";i:1565167658;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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
       
      
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5><a href="javascript:history.back();"><i class="fa fa-chevron-left"></i> 返回 </a></h5>
                        <div class="ibox-tools">
                           修改干线班次
                        </div>
                    </div>
                    <div class="ibox-content">
                        <form class="form-horizontal m-t" id="commentForm" method="post" action="/admin/shift/updatemessage">
                             <div class="form-group">
                                <label class="col-sm-2 control-label">班次号<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="ShiftNumber" placeholder="请填写班次(公司前两首字母+始发城市首字母+目的城市首字母+周几+第几班)" value="<?php echo $list['shiftnumber']; ?>" name="ShiftNumber" minlength="2" type="text" class="form-control" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">公司名称<span style="color:red"> *</span></label>
                                <div class="col-sm-10">
                                    <input id="key" placeholder="请填选承运公司名称" value="<?php echo $list['name']; ?>" name="name" minlength="2" type="text" class="form-control" required="" aria-required="true">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                             <div class="form-group">
                                <label class="col-sm-2 control-label">始发地 </label>
                                <div class="col-sm-3" style="padding-top:7px">
                                    <?php echo $list['linestart']; ?>
                                </div>
                            </div>
                             <div class="form-group selectAddress">
                                <label class="col-sm-2 control-label">重选始发地 </label>
                                <div class="col-sm-2">
                                    <select  class="form-control pro" name="tpro"></select>
                                </div>
                                <div class="col-sm-2">
                                    <select  class="form-control city" name="tcity"></select>
                                </div>
                            </div>
                           
                            <div class="hr-line-dashed"></div>
                              <div class="form-group">
                                <label class="col-sm-2 control-label">终点地 </label>
                                <div class="col-sm-3" style="padding-top:7px">
                                    <?php echo $list['lineend']; ?>
                                </div>
                            </div>  
                            <div class="form-group selectAddress">
                                <label class="col-sm-2 control-label">重选终点地</label>
                                <div class="col-sm-2">
                                    <select  class="form-control pro" name="ppro"></select>
                                </div>
                                <div class="col-sm-2">
                                    <select  class="form-control city" name="pcity"></select>
                                </div>
                            </div>
                       <div class="hr-line-dashed"></div>
                       <div class="form-group">
                            <label class="col-sm-2 control-label">免费提货重量限制(kg)</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" value="<?php echo $list['freetonnage']; ?>" id="FreeTonnage" name="FreeTonnage"  placeholder="请填写免费提货最小重量要求(kg/提货点)">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">干线泡货价(元)</label>
                            <div class="col-sm-10">
                                <input type="text" value="<?php echo $list['eprice']; ?>"  class="form-control" id="Eprice" name="Eprice" placeholder="请填写干线泡货立方价(元/立方)" >
                            </div>
                        </div>                    
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">干线最低运价</label>
                            <div class="col-sm-10">
                                <input type="text" value="<?php echo $list['price']; ?>"  class="form-control" id="Price" name="Price" placeholder="请填写干线最低运价(元/公斤)" readonly/>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">干线最低收费<span style="color:red"> *</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="<?php echo $list['lowprice']; ?>" class="form-control" id="lowprice" name="lowprice" placeholder="干线班次最低收费价格" required="" aria-required="true">
                            </div>
                        </div>
                        
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">添加</label>
                            <div class="col-sm-10 col-xs-12" ><a id="AddMoreFileBox"  class="btn btn-info">添加更多的干线价格费用率约束条件</a></div>
                        </div>

                       <div class="form-group" id="InputsWrapper">
                        <?php if(isset($list['free'])): if(is_array($list['free']) || $list['free'] instanceof \think\Collection || $list['free'] instanceof \think\Paginator): $i = 0; $__LIST__ = $list['free'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
                            <div class="zhongliangtext" style="height: 45px;">
                                
                                <label class="col-sm-2 control-label">重量范围：</label> 
                                
                                <div class="col-sm-10 col-xs-12" >
                                    <i class="col-sm-2 col-lg-3 "><input type="hidden" id="sfid" name="sfid[]" value="<?php echo $val['sfid']; ?>" /><input type="text" value="<?php echo $val['starweight']; ?>" class="inputos form-control " name="mytext1[]" id="field1_1" /></i>
                                    <p class="col-sm-1 col-lg-1  form-control-static">KG 到</p>
                                    <i class="col-sm-2 col-lg-3"><input type="text"  value="<?php echo $val['endweight']; ?>" class="inputos form-control" name="mytext2[]" id="field2_1"/></i>
                                    <p class="col-sm-1 col-lg-1  form-control-static">KG</p>
                                    <p class="col-sm-1 col-lg-1  form-control-static">价格</p>
                                    <i class="col-sm-2 col-lg-2 "><input type="text" value="<?php echo $val['freeprice']; ?>" class="inputos form-control" name="mytext3[]" id="field3_1" placeholder="请填写￥/KG" /></i>
                                    <a href="#" class="removeclass col-sm-1 col-lg-1  form-control-static">×</a>
                                </div> 
                                
                            </div>
                            <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </div>  
                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <div class="col-sm-6 col-xs-12 telescopic">
                                <label class="col-sm-4 control-label">发车窗口(每周)</label>
                                <div class="col-sm-8">
                                <select class="form-control m-b" style="height:40px;" id="DeWin" name="DeWin" >
                               
                                <?php if(is_array($arr) || $arr instanceof \think\Collection || $arr instanceof \think\Paginator): if( count($arr)==0 ) : echo "" ;else: foreach($arr as $k=>$vo): ?>
                                <option <?php if(($list['dewin'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $vo; ?> </option>
                               
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xs-12 telescopic">
                                <label class="col-sm-4 control-label">发车时段</label>
                                <div class="col-sm-8">
                                    <div class="col-sm-5">
                                        <select class="form-control m-b" id="TimeStrat" name="TimeStrat"  style="max-height:50px; overflow-y:auto">
                                        <?php if(is_array($tim) || $tim instanceof \think\Collection || $tim instanceof \think\Paginator): if( count($tim)==0 ) : echo "" ;else: foreach($tim as $k=>$valu): ?>
                                            <option <?php if(($list['timestrat'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $valu; ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </select>
                                    </div>
                                    <i class="col-sm-2 fa fa-arrows-h" style="text-align:center; font-size:20px; line-height:34px;"></i>
                                    <div class="col-sm-5">
                                        <select class="form-control m-b" id="TimeEnd" name="TimeEnd"  style="max-height:50px; overflow-y:auto">
                                       <?php if(is_array($tim) || $tim instanceof \think\Collection || $tim instanceof \think\Paginator): if( count($tim)==0 ) : echo "" ;else: foreach($tim as $k=>$valu): ?>
                                            <option <?php if(($list['timeend'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $valu; ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6 telescopic">
                                <label class="col-sm-4 control-label">自行送货截止时间(提前量)：</label>
                                <div class="col-sm-8">
                                    <select class="form-control m-b"  id="SelfDeliveryDeadline" name="SelfDeliveryDeadline"  style="max-height:50px; overflow-y:auto">
                                    
                                    
                                      <?php if(is_array($hou) || $hou instanceof \think\Collection || $hou instanceof \think\Paginator): if( count($hou)==0 ) : echo "" ;else: foreach($hou as $k=>$value): ?>
                                      <option <?php if(($list['selfdeliverydeadline'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $value; ?></option>
                                     <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <label class="col-sm-2 control-label">始发仓地址：</label>
                            <div class="col-sm-10" style="padding-top:7px">
                                <?php echo $list['shifa']; ?>
                            </div>
                        </div>
                         <div class="form-group selectAddress">
                            <label class="col-sm-2 control-label">重填始发仓地址：</label>
                            <div class="col-sm-10">
                                <div class="row">
                                <div class="col-sm-3">
                                    <select class="form-control pro" name="sheng" ></select>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control city" name="shi"></select>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control area" name="xian"></select>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" name="beginAddress" placeholder="请填详细写地址" />
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">仓库收货时间段</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="" name="stime" value="<?php echo $list['stime']; ?>" placeholder="请填写时间段格式：10:00-18:00">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">始发仓联系人</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="" name="sphone" value="<?php echo $list['sphone']; ?>" placeholder="请填写联系人电话">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">时效（D）：</label>
                            <div class="col-sm-3">
                                <select class="form-control m-b" id="TrunkAging" name="TrunkAging">
                                <?php if(is_array($day) || $day instanceof \think\Collection || $day instanceof \think\Paginator): if( count($day)==0 ) : echo "" ;else: foreach($day as $k=>$vo): ?>
                                
                                <option <?php if(($list['trunkaging'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $vo; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <div class="col-sm-6 col-xs-12 telescopic">
                                <label class="col-sm-4 control-label">到车窗口（每周）：</label>
                                <div class="col-sm-8">
                                    <div class="col-sm-6 col-xs-12 row">
                                        <select class="form-control m-b" style="height:40px;" id="ArriveWin" name="ArriveWin" >
                                        <?php if(is_array($arr) || $arr instanceof \think\Collection || $arr instanceof \think\Paginator): if( count($arr)==0 ) : echo "" ;else: foreach($arr as $k=>$vo): ?>
                                        <option <?php if(($list['arrivewin'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $vo; ?> </option>
                                       
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xs-12 telescopic">
                                <label class="col-sm-4 control-label">到车时段：</label>
                                <div class="col-sm-8">
                                    <div class="col-sm-5 col-xs-5 row">
                                        <select class="form-control m-b" id="ArriveTimeStart" name="ArriveTimeStart"  style="max-height:50px; overflow-y:auto">
                                        <?php if(is_array($tim) || $tim instanceof \think\Collection || $tim instanceof \think\Paginator): if( count($tim)==0 ) : echo "" ;else: foreach($tim as $k=>$value): ?>
                                          <option  <?php if(($list['arrivetimestart'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $value; ?></option>
                                         <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </select>
                                    </div>
                                    <i class="col-sm-2 col-xs-2 fa fa-arrows-h" style="text-align:center; font-size:20px; line-height:34px;"></i>
                                    <div class="col-sm-5 col-xs-5">
                                        
                                        <select class="form-control m-b" id="ArriveTimeEnd" name="ArriveTimeEnd" style="max-height:50px; overflow-y:auto">
                                       <?php if(is_array($tim) || $tim instanceof \think\Collection || $tim instanceof \think\Paginator): if( count($tim)==0 ) : echo "" ;else: foreach($tim as $k=>$value): ?>
                                          <option  <?php if(($list['arrivetimeend'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $value; ?></option>
                                         <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-6 telescopic">
                                <label class="col-sm-4 control-label">自行提货最早时间(延迟量)：</label>
                                <div class="col-sm-8">
                                    <select class="form-control m-b" id="MorningTime" name="MorningTime"  style="max-height:50px; overflow-y:auto">
                                    <?php if(is_array($hou) || $hou instanceof \think\Collection || $hou instanceof \think\Paginator): if( count($hou)==0 ) : echo "" ;else: foreach($hou as $k=>$value): ?>
                                      <option <?php if(($list['morningtime'] == $k)): ?> selected = selected<?php endif; ?>><?php echo $value; ?></option>
                                     <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <label class="col-sm-2 control-label">终点仓地址：</label>
                            <div class="col-sm-10" style="padding-top:7px">
                               <?php echo $list['zhongdiancan']; ?>
                            </div>
                        </div>
                       
                         <div class="form-group selectAddress">
                            <label class="col-sm-2 control-label">重填终点仓地址：</label>
                            <div class="col-sm-10">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <select class="form-control pro" name="sheng1"></select>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control city" name="shi1" ></select>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control area"  name="xian1"></select>
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="text" placeholder="请填详细写地址"  name="endAddress"  class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">仓库提货时间段</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="" name="dtime" value="<?php echo $list['dtime']; ?>"  placeholder="请填写时间段格式：10:00-18:00">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">终点仓联系人</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="" name="tphone"  value="<?php echo $list['tphone']; ?>" placeholder="请填写联系人电话">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <div class="col-sm-6 telescopic">
                                <label class="col-sm-4 control-label">折扣：</label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="text" name="discount" value="<?php echo $list['discount']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">是否开启 </label>
                            <div class="col-sm-10">
                                <div class="radio i-checks">
                                    <label><input type="radio" value="2" <?php if(($list['whethertoopen'] ==2)): ?>checked=""<?php endif; ?> name="whethertoopen"><i></i>关</label>
                                </div>
                                <div class="radio i-checks">
                                    <label><input type="radio"  value="1"   <?php if(($list['whethertoopen'] ==1)): ?>checked=""<?php endif; ?> name="whethertoopen"><i></i> 开 </label>
                                </div>
                            </div>
                        </div>

                       

                        <div class="hr-line-dashed"></div>   
                            
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="sid" value=<?php echo $list['sid']; ?>>
                                    <input type="hidden" name="alrcityid" value=<?php echo $list['linecityid']; ?>>
                                    <button class="btn btn-primary" type="submit">保存内容</button>
                                    <a class="btn btn-danger" onClick="javascript :history.back(-1);"  style="width:82px;">取消</a>
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
   
    <!--<script src="/static/tpl/js/shiftPage.js"></script>-->
    <script language="javascript" src="/static/tpl/ui/form.js"></script>
    <link rel="stylesheet" href="/static/tpl/ui/jquery.ui.autocomplete.css">
    <script type="text/javascript" src="/static/tpl/ui/jquery.min.js"></script>
    <script type="text/javascript" src="/static/tpl/ui/jquery.ui.core.js"></script>
    <script type="text/javascript" src="/static/tpl/ui/jquery.ui.widget.js"></script>
    <script type="text/javascript" src="/static/tpl/ui/jquery.ui.position.js"></script>
    <script type="text/javascript" src="/static/tpl/ui/jquery.ui.autocomplete.js"></script>
     <!-- iCheck -->
    <script src="/static/tpl/js/plugins/iCheck/icheck.min.js"></script>
     <!-- 地址三级联动 -->
    <script src="/static/tpl/js/common/select-address.js"></script>
    <script>
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    });
    $(function() {
        var maxELe = 25;
        var FieldCount = 1;
        $("#AddMoreFileBox").click(function() {
            var eleLeng = $("#InputsWrapper").find(".zhongliangtext").length;

            /*if(eleLeng == 1){
                $("#InputsWrapper").find("input").val("");
            }*/

            if(FieldCount < maxELe){
                FieldCount = FieldCount + 1;
                var ele = '<div class="zhongliangtext" style="height: 45px;"><label class="col-sm-2 col-xs-12 control-label zhongliangfanwei">重量范围：</label><span class="col-sm-10 col-xs-12" ><i class="col-sm-2 col-lg-3 col-xs-3"><input type="hidden" name="sfid[]" value=""><input type="text" name="mytext1[]" class="inputos form-control row" id="field1_'+ FieldCount +'" /></i><p class="col-sm-2 col-lg-1 col-xs-3">KG 到</p><i class="col-sm-2 col-lg-3 col-xs-3"><input type="text" name="mytext2[]" class="inputos form-control" id="field2_'+ FieldCount +'" /></i><p class="col-sm-1 col-lg-1 col-xs-3">KG</p><p class="col-sm-2 col-lg-1 col-xs-3">价格</p><i class="col-sm-2 col-lg-2 col-xs-7"><input type="text" name="mytext3[]" class="inputos form-control" id="field3_'+ FieldCount +'" placeholder="请填写￥/KG" /></i><a href="#" class="removeclass col-sm-1 col-lg-1 col-xs-2">×</a></span></div>';
                $("#InputsWrapper").append(ele);
            }else{
                return false;
            }
        });

        $("body").on("click",".removeclass", function(e){
            var eleLeng = $("#InputsWrapper").find(".zhongliangtext").length;
            var getid = $(this).parent().find("input[type='hidden']").val();
            if(getid !='' || getid==undefined){
                
                $.post("/admin/shift/del",{ajax:1,sfid:getid},function(result){
                   
                  });
            }
           
            //if( eleLeng > 1 ) {
               $(this).parents('.zhongliangtext').remove();
                FieldCount--; //decrement textbox
            //}
            return false;
        })

        $("#ResidualWeight").blur(function(){

            var eleLeng = $("#InputsWrapper").find(".zhongliangtext").length;
            var val = $(this).val();
            
            if(eleLeng == 1){
                $("#field2_1").val(val);
            }
        });

        $("#Price").blur(function(){

            var eleLeng = $("#InputsWrapper").find(".zhongliangtext").length;
            var val = $(this).val();

            if(eleLeng == 1){
                $("#field3_1").val(val);
            }
        });
    });  
    </script>

    <script type="text/javascript">
        $(function(){
            $( "#key" ).autocomplete({
                source: "/admin/common/search",
                minLength: 2,
                autoFocus: true
            });
        });
    </script>
</body>

</html>
