<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:75:"D:\WWW\chitu_admin\public/../application/admin\view\order\orderdetails.html";i:1563955704;s:70:"D:\WWW\chitu_admin\public/../application/admin\view\public\header.html";i:1561462978;}*/ ?>
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


    <link rel="stylesheet" type="text/css" href="/static/tpl/css/common/search.css"/>
    <link href="/static/tpl/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
    <link href="/static/tpl/css/boxImg.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .lightBoxGallery{
            display: flex;
            flex-direction: row;
        }
        .lightBoxGallery a {
            display: flex;
            margin: 10px;
            width: 150px;
            height: 100px;
            overflow: hidden;
            border: 5px solid #f3f3f3;
            border-radius: 5px; 
        }
        .lightBoxGallery img {
            margin: auto;
            width: 160px;
        }
        .user-message {
            padding: 10px 20px;
        }
        .company-message{
            padding: 10px 20px;
        }
        .user-message .message-avatar.user-avatar-right{
            float:right;
            margin-left: 10px;
        }
        .user-message .message-avatar.user-avatar-left{
            float:left;
            margin-right: 10px;
        }
        .company-message .message-avatar.user-avatar-right{
            float:right;
            margin-left: 10px;
        }
        .company-message .message-avatar.user-avatar-left{
            float:left;
            margin-right: 10px;
        }
        .user-message .message {
            text-align: left;
            margin-left: 55px;
        }
        .company-message{
            padding: 10px 20px;
        }
        .company-message .message {
            text-align: right;
            margin-right: 55px;
        }
    </style> 
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content animated fadeInUp">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><a href="javascript:history.back();"><i class="fa fa-chevron-left"></i> 返回 </a></h5>
                <div class="ibox-tools">客户订单详情</div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                 <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>订单详情</h5>
                    </div>
                    <div class="ibox-content m-b-sm border-bottom">
                        <div class="row">
                            <div class="col-sm-2">订单编号:</div>
                            <div class="col-sm-10"><?php echo $list['ordernumber']; ?></div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">下单公司:</div>
                            <div class="col-sm-10"><?php echo $list['company']; ?></div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">下单客户:</div>
                            <div class="col-sm-10"><?php echo $list['realname']; ?> (TEL:<?php echo $list['phone']; ?>)</div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        
                        <div class="row">
                            <div class="col-sm-2">班次信息:</div>
                            <div class="col-sm-10">
                                <b><?php echo $list['drivercom']; ?> </b>
                                <span>班次号为 <b><?php echo $list['shiftnumber']; ?></b> 的班次</span>
                                <?php if(($list['shiftstate']=='1')): ?>
                                <span>由 <?php echo date('Y年m月d日',$list['deptime']); ?> <?php echo $list['timestrat']; ?> - <?php echo $list['timeend']; ?></span>
                                <b>出发 从</b>
                                <span class="label label-primary"> <?php echo $list['startcity']; ?></span> 
                                <b>发往</b> 
                                <span class="label label-primary"><?php echo $list['endcity']; ?></span> 
                                <span><?php echo date('Y年m月d日',$list['endtime']); ?> <?php echo $list['arrivetimestart']; ?> - <?php echo $list['arrivetimeend']; ?></span>
                                <b>到达</b>
                                <?php endif; ?>

                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        

                        <div class="row">
                            <div class="col-sm-2">提货时间:</div>
                            <div class="col-sm-10"><?php echo $list['picktime']; ?></div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <?php if($list['picktype'] == 1): ?>
                        <div class="row">
                            <div class="col-sm-2">提货地址:</div>
                            <div class="col-sm-10">
                                <?php if(isset($list['taddress'])): if(is_array($list['taddress']) || $list['taddress'] instanceof \think\Collection || $list['taddress'] instanceof \think\Paginator): $key = 0; $__LIST__ = $list['taddress'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                                    <?php echo $list['startcity']; ?><?php echo $vo; ?> 、
                                <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-sm-2">提货地址:</div>
                            <div class="col-sm-10">
                               <span class="label label-info">自送到点</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="hr-line-dashed"></div>

                        <?php if($list['sendtype'] == 1): ?>
                        <div class="row">
                            <div class="col-sm-2">配送地址:</div>
                            <div class="col-sm-10">
                                <?php if(isset($list['paddress_arr'])): if(is_array($list['paddress_arr']) || $list['paddress_arr'] instanceof \think\Collection || $list['paddress_arr'] instanceof \think\Paginator): $key = 0; $__LIST__ = $list['paddress_arr'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                                    <span><?php echo $vo['address']; ?></span>
                                    <span class="text-danger"><?php echo $vo['contact']; ?></span>
                                <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-sm-2">配送地址:</div>
                            <div class="col-sm-10">
                                <span class="label label-info">到点自提</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">物品类别:</div>
                            <div class="col-sm-10"><?php echo $list['itemtype']; ?></div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">冷藏类型:</div>
                            <div class="col-sm-10"><?php echo $list['coldtype']; ?></div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">货物规格:</div>
                            <div class="col-sm-10"><?php echo $list['totalnumber']; ?>件 <?php echo $list['totalweight']; ?>公斤 <?php echo $list['totalvolume']; ?>立方</div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">订单备注:</div>
                            <div class="col-sm-10"><?php echo $list['remark']; ?></div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">提货运费:</div>
                            <div class="col-sm-10"><?php echo number_format($list['tprice'],2); ?> 元</div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">干线运费:</div>
                            <div class="col-sm-10"><?php echo number_format($list['linepice'],2); ?> 元</div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">配送运费:</div>
                            <div class="col-sm-10"><?php echo number_format($list['delivecost'],2); ?> 元</div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">总计运费:</div>
                            <div class="col-sm-10"><?php echo number_format($list['tprice']+$list['linepice']+$list['delivecost'],2); ?> 元</div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="row">
                            <div class="col-sm-2">回单:</div>
                            <div class="col-sm-10">
                                <div class="lightBoxGallery">
                                    <?php if(!empty($list['picture'])): if(is_array($list['picture']) || $list['picture'] instanceof \think\Collection || $list['picture'] instanceof \think\Paginator): $i = 0; $__LIST__ = $list['picture'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                        <a href="<?php echo $vo; ?>" title="回单" data-gallery=""><img modal="rotate" src="<?php echo $vo; ?>"></a>
                                        <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                    <!--<div id="blueimp-gallery" class="blueimp-gallery">
                                        <div class="slides"></div>
                                        <h3 class="title"></h3>
                                        <a class="prev"><</a>
                                        <a class="next">></a>
                                        <a class="close">×</a>
                                        <a class="play-pause"></a>
                                        <ol class="indicator"></ol>
                                    </div>-->
                                </div>
                                
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </div>  
                </div>
            </div>
            <div class="col-xs-6">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>操作</h5>
                    </div>
                    <div class="ibox-content m-b-sm border-bottom">
                        <div class="row">
                            <div class="col-sm-2">总计运费:</div>
                            <div class="col-sm-10"><?php echo number_format($list['tprice']+$list['linepice']+$list['delivecost'],2); ?> 元</div>
                        </div>
                        <div class="hr-line-dashed"></div>

                        <div class="row">
                            <div class="col-sm-2">已修改总价:</div>
                            <div class="col-sm-10">
                                <div class="form-group">
                                    暂未开通此功能
                                <!--<?php if(($list['totalcost'] !='')): ?>
                                    <?php echo number_format($list['totalcost'],2); ?> 元
                                <?php endif; ?>-->
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <?php if(($list['instate'] =='1' || $list['instate'] =='')): ?>
                        <div class="row">
                            <div class="col-sm-2" style="margin-top: 7px">修改总价:</div>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="hidden" class="ordernum" value="<?php echo $list['oid']; ?>">
                                    <input type="text" class="form-control newprice" value="" /> 
                                    <span class="input-group-btn">
                                        <!--<button type="button" id="updateTotal" class="btn btn-primary">修改</button>-->
                                        <button type="button" class="btn btn-primary">修改</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <?php endif; ?>
                    
                        <div class="row">
                            <div class="col-sm-2">承运运费:</div>
                            <div class="col-sm-10"> 暂未开通此功能</div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <?php if(($list['totalcost'] !='')): ?>
                        <div class="row">
                            <div class="col-sm-2">已修改承运费:</div>
                            <div class="col-sm-10">暂未开通此功能</div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <?php endif; if(($list['instate'] =='1' || $list['instate'] =='')): ?>
                        <div class="row">
                            <div class="col-sm-2" style="margin-top: 7px">修改承运费:</div>
                            <div class="col-sm-10">
                                <div class="form-horizontal">
                                    <div class="input-group">
                                        <input type="text" value="" class="form-control"> 
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary">修改</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>意见反馈</h5>
                    </div>
                    <div class="ibox-content m-b-sm border-bottom">
                        <div class="row">
                            <div class="col-sm-12 ">
                                <div class="chat-discussion">
                                    <?php if(is_array($list['contact']) || $list['contact'] instanceof \think\Collection || $list['contact'] instanceof \think\Paginator): $i = 0; $__LIST__ = $list['contact'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <div class='<?php if(($vo['utype']==1)): ?>user-message <?php else: ?> company-message <?php endif; ?>'>
                                        <?php if(($vo['image'] =='')): ?>
                                            <img class='message-avatar <?php if(($vo['utype']==2)): ?>user-avatar-right<?php else: ?>user-avatar-left<?php endif; ?>'  src="/static/user_header.png" alt="">
                                        <?php else: ?>
                                            <img class='message-avatar <?php if(($vo['utype']==2)): ?>user-avatar-right<?php else: ?>user-avatar-left<?php endif; ?>'  src="<?php echo $vo['image']; ?>" alt="">
                                            
                                        <?php endif; ?>
                                        <div class="message">
                                            <a class="message-author" href="#"><?php echo $vo['realname']; ?></a>
                                            <span class="message-date"> <?php echo date('Y-m-d H:i:s',$vo['addtime']); ?> </span>
                                            <span class="message-content">
                                            <?php echo $vo['message']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                    <!--<div class="chat-message">
                                        <img class="message-avatar" style="float:right;margin-left: 10px" src="/static/user_header.png" alt="">
                                        <div class="message">
                                            <a class="message-author" href="#"> 林依晨Ariel </a>
                                            <span class="message-date">  2015-02-02 11:12:36 </span>
                                            <span class="message-content">
                                            jQuery表单验证插件 - 让表单验证变得更容易
                                            </span>
                                        </div>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="chat-message-form">

                                    <div class="form-group">

                                        <textarea class="form-control message-input info" name="message" placeholder="输入消息内容，按回车键发送"></textarea>
                                    </div>
                                    <button type="button" id="contact" class="btn btn-primary" style="margin-top: 10px;float:right">提交</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</body>
<script src="/static/tpl/js/jquery.min.js?v=2.1.4"></script>
<script type="text/javascript">
     $(document).ready(function() {
        // 获取url附带参数
        function getRequest(strName){
            var strHref = location.search;
            var intPos = strHref.indexOf("?");
            var strRight = strHref.substr(intPos + 1);
            var arrTmp = strRight.split("&");
            for(var i = 0; i < arrTmp.length; i++) {
                var arrTemp = arrTmp[i].split("=");
                if(arrTemp[0].toUpperCase() == strName.toUpperCase())return arrTemp[1];
            }
            return "Request(strName)";
        } 
        $("#updateTotal").click(function(){
            var id = $(".ordernum").val();
            var price = $(".newprice").val();
            if (price =='') {
                alert('请填写要修改的总价');
                return false;
            };
            $.post('/admin/order/upprice',{ajax:1,oid:id,price:price},function(data){
                        window.history.back();
            });
        });
        $("#contact").click(function(){
            var newId = getRequest("id");
            var message = $(".info").val();
           if (message =='') {
                alert('请填写回复内容');
                return false;
            };
            $.post('/admin/order/replay_contact',{ajax:'1',otype:'1',oid:newId,message:message},function(data){
                    window.history.back();
            });
        });
    })
</script>